<script setup lang="ts">
import { ref, computed, onUnmounted, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
import type { Preview } from '@/types';

import FloatingInput from '@/components/FloatingInput.vue';
import { Button } from '@/components/ui/button';
import { XIcon, PlusIcon } from 'lucide-vue-next';

const props = withDefaults(defineProps<{
    collectionName?: string;
    collectionLabel?: string;
    hideCollectionInput?: boolean;
    maxPhotos?: number;
}>(), {
    collectionName: '',
    collectionLabel: 'Collection name',
    hideCollectionInput: false,
    maxPhotos: 5,
});

const MIN_WIDTH = 500;
const MIN_HEIGHT = 500;
const MAX_FILE_SIZE_MB = 5;
const MAX_FILE_SIZE_BYTES = MAX_FILE_SIZE_MB * 1024 * 1024;
const MAX_LONG_EDGE = 2000;


const inputId = `media-file-input-${Math.random().toString(36).slice(2)}`;
const fileInputRef = ref<HTMLInputElement | null>(null);
const mediaPreviews = ref<Preview[]>([]);
const clientSideErrors = ref<string[]>([]);

const inertiaForm = useForm({
    collection_name: props.collectionName ?? '',
    images: [] as File[],
});

const resolvedMaxPhotos = computed(() => {
    const value = Number(props.maxPhotos);
    return Number.isFinite(value) && value > 0 ? value : 5;
});

const collectionLabel = computed(() => props.collectionLabel || 'Collection name');

watch(
    () => props.collectionName,
    (value) => {
        if (value) {
            inertiaForm.collection_name = value;
        }
    }
);

const loadImageElement = (file: File): Promise<HTMLImageElement> => {
    return new Promise((resolve, reject) => {
        const img = new Image();
        const objectUrl = URL.createObjectURL(file);
        img.onload = () => {
            URL.revokeObjectURL(objectUrl);
            resolve(img);
        };
        img.onerror = () => {
            URL.revokeObjectURL(objectUrl);
            reject(new Error('Invalid image'));
        };
        img.src = objectUrl;
    });
};

const canvasToBlob = (canvas: HTMLCanvasElement, type: string, quality: number): Promise<Blob | null> => {
    return new Promise((resolve) => {
        canvas.toBlob((blob) => resolve(blob), type, quality);
    });
};

const buildCompressedFileName = (originalName: string, mimeType: string) => {
    const base = originalName.replace(/\.[^/.]+$/, '');
    const extension = mimeType === 'image/jpeg'
        ? 'jpg'
        : mimeType === 'image/png'
            ? 'png'
            : 'webp';
    return `${base}.${extension}`;
};

const compressImageToLimit = async (
    file: File,
    img: HTMLImageElement
): Promise<{ file: File; wasCompressed: boolean } | null> => {
    const outputType = 'image/jpeg';
    const qualitySteps = [0.9, 0.82, 0.74, 0.66, 0.6];
    const maxEdge = Math.max(img.naturalWidth, img.naturalHeight);
    const scale = maxEdge > MAX_LONG_EDGE ? MAX_LONG_EDGE / maxEdge : 1;

    let targetWidth = Math.round(img.naturalWidth * scale);
    let targetHeight = Math.round(img.naturalHeight * scale);

    for (let scaleAttempt = 0; scaleAttempt < 4; scaleAttempt += 1) {
        if (targetWidth < MIN_WIDTH || targetHeight < MIN_HEIGHT) {
            break;
        }

        const canvas = document.createElement('canvas');
        canvas.width = targetWidth;
        canvas.height = targetHeight;
        const ctx = canvas.getContext('2d');
        if (!ctx) {
            return null;
        }
        ctx.drawImage(img, 0, 0, targetWidth, targetHeight);

        for (const quality of qualitySteps) {
            const blob = await canvasToBlob(canvas, outputType, quality);
            if (!blob) {
                continue;
            }
            if (blob.size <= MAX_FILE_SIZE_BYTES) {
                const fileName = buildCompressedFileName(file.name, outputType);
                return {
                    file: new File([blob], fileName, { type: outputType }),
                    wasCompressed: true,
                };
            }
        }

        targetWidth = Math.round(targetWidth * 0.85);
        targetHeight = Math.round(targetHeight * 0.85);
    }

    return null;
};

const onFileChange = async (event: Event) => {
    const target = event.target as HTMLInputElement;
    const files = target.files;
    if (!files) return;

    const currentClientErrors: string[] = [];
    const newPreviewsToAdd: Preview[] = [];

    for (const file of Array.from(files)) {
        if (mediaPreviews.value.length + newPreviewsToAdd.length >= resolvedMaxPhotos.value) {
            currentClientErrors.push(`You can only upload up to ${resolvedMaxPhotos.value} photos.`);
            break;
        }
        if (!file.type.startsWith('image/')) {
            currentClientErrors.push(`File ${file.name} is not an image.`);
            continue;
        }
        let img: HTMLImageElement | null = null;
        try {
            img = await loadImageElement(file);
        } catch (error) {
            currentClientErrors.push(`File ${file.name} could not be read as an image.`);
            continue;
        }

        if (img.naturalWidth < MIN_WIDTH || img.naturalHeight < MIN_HEIGHT) {
            currentClientErrors.push(`Image ${file.name} dimensions must be at least ${MIN_WIDTH}x${MIN_HEIGHT}px.`);
            continue;
        }

        let fileToUse = file;
        if (file.size > MAX_FILE_SIZE_BYTES) {
            const compressed = await compressImageToLimit(file, img);
            if (!compressed) {
                currentClientErrors.push(`File ${file.name} exceeds the maximum size of ${MAX_FILE_SIZE_MB}MB.`);
                continue;
            }
            fileToUse = compressed.file;
        }

        const url = URL.createObjectURL(fileToUse);
        newPreviewsToAdd.push({ file: fileToUse, preview: url });
    }

    clientSideErrors.value = currentClientErrors;
    if (newPreviewsToAdd.length > 0) {
        mediaPreviews.value = [...mediaPreviews.value, ...newPreviewsToAdd];
    }

    if (fileInputRef.value) {
        fileInputRef.value.value = '';
    }
};

const removePhoto = (idx: number) => {
    const removed = mediaPreviews.value.splice(idx, 1);
    if (removed.length > 0) {
        URL.revokeObjectURL(removed[0].preview);
    }
};

const resetAllClientState = () => {
    mediaPreviews.value.forEach((p) => URL.revokeObjectURL(p.preview));
    mediaPreviews.value = [];
    clientSideErrors.value = [];
    inertiaForm.reset();
    if (props.collectionName) {
        inertiaForm.collection_name = props.collectionName;
    }
    if (fileInputRef.value) {
        fileInputRef.value.value = '';
    }
};

const canUpload = computed(() => {
    return mediaPreviews.value.length > 0 && inertiaForm.collection_name.trim() !== '' && !inertiaForm.processing;
});

const upload = () => {
    if (!canUpload.value) return;
    clientSideErrors.value = [];

    inertiaForm.images = mediaPreviews.value.map(p => p.file);

    inertiaForm.post('/settings/media', {
        preserveScroll: true,
        onSuccess: () => {
            resetAllClientState();
        },
        onError: (errors: Record<string, string[]>) => {
            console.error("Upload failed with errors:", errors);
        },
    });
};

onUnmounted(() => {
    mediaPreviews.value.forEach((p) => URL.revokeObjectURL(p.preview));
});

</script>

<template>
    <form @submit.prevent="upload" enctype="multipart/form-data" class="space-y-4">
        <h2 class="text-lg font-semibold text-gray-900 dark:text-neutral-200">
            Upload Media
        </h2>
        <div v-if="!props.hideCollectionInput" class="grid w-full max-w-sm items-center gap-1.5">
            <FloatingInput
                id="collection_name"
                :label="collectionLabel"
                name="collection_name"
                type="text"
                v-model="inertiaForm.collection_name"
                :disabled="inertiaForm.processing"
            />
            <span v-if="inertiaForm.errors.collection_name" class="text-sm text-red-500 mt-1">
                {{ inertiaForm.errors.collection_name }}
            </span>
        </div>

        <div>
            <p class="block mb-2 text-sm font-medium text-gray-800 dark:text-neutral-200">
                Add photos (max {{ resolvedMaxPhotos }}, {{ MIN_WIDTH }}x{{ MIN_HEIGHT }}px min, {{ MAX_FILE_SIZE_MB }}MB max per image)
            </p>
            <div class="flex flex-wrap gap-2">
                <label :for="inputId"
                    :class="['flex shrink-0 justify-center items-center w-32 h-32 border-2 border-dotted border-gray-300 rounded-xl text-gray-400 cursor-pointer hover:bg-gray-50 dark:border-neutral-700 dark:text-neutral-600 dark:hover:bg-neutral-700/20',
                        { 'opacity-50 cursor-not-allowed': mediaPreviews.length >= resolvedMaxPhotos || inertiaForm.processing }]">
                    <input ref="fileInputRef" :id="inputId" type="file" accept="image/*" multiple class="hidden"
                        @change="onFileChange"
                        :disabled="mediaPreviews.length >= resolvedMaxPhotos || inertiaForm.processing" />
                    <PlusIcon class="w-6 h-6" />
                </label>

                <div v-for="(mp, idx) in mediaPreviews" :key="mp.preview"
                    class="relative w-32 h-32 rounded-xl overflow-hidden border border-gray-300 dark:border-neutral-700">
                    <img :src="mp.preview" class="object-cover w-full h-full" :alt="`Preview ${idx + 1}`" />
                    <button type="button" @click="() => removePhoto(idx)" :disabled="inertiaForm.processing"
                        class="absolute top-1 right-1 p-1 bg-white rounded-full shadow-md hover:bg-gray-100 dark:bg-neutral-800 dark:hover:bg-neutral-700"
                        aria-label="Remove photo">
                        <XIcon class="w-4 h-4 text-gray-500 dark:text-neutral-400" />
                    </button>
                </div>
            </div>
            <p class="mt-2 text-xs text-gray-500 dark:text-neutral-500">
                Shoppers find images more helpful than text alone.
            </p>
        </div>

        <div v-if="clientSideErrors.length > 0" class="text-red-500 text-sm space-y-1">
            <div v-for="(error, i) in clientSideErrors" :key="`client-err-${i}`">{{ error }}</div>
        </div>
        <div v-if="inertiaForm.hasErrors" class="text-red-500 text-sm space-y-1 mt-2">
            <div v-if="inertiaForm.errors.images && typeof inertiaForm.errors.images === 'string'">{{
                inertiaForm.errors.images }}</div>
            <template v-else-if="inertiaForm.errors.images && typeof inertiaForm.errors.images === 'object'">
                <div v-for="(imgError, imgKey) in (inertiaForm.errors.images as Record<string, string>)"
                    :key="`server-img-err-${imgKey}`">
                    {{ imgError }}
                </div>
            </template>
            <div v-for="(error, key) in inertiaForm.errors" :key="`server-err-${key}`">
                <template v-if="key !== 'collection_name' && key !== 'images'">{{ error }}</template>
            </div>
        </div>


        <div class="flex mt-6 space-x-3">
            <Button type="submit" :disabled="!canUpload" :class="{ 'opacity-50 cursor-not-allowed': !canUpload }">
                <span v-if="inertiaForm.processing">Uploading...</span>
                <span v-else>Upload</span>
            </Button>
            <Button type="button" variant="outline" @click="resetAllClientState" :disabled="inertiaForm.processing">
                Reset
            </Button>
        </div>
    </form>
</template>

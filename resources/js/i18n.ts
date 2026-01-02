import { createI18n } from 'vue-i18n';

export type I18nPageProps = {
    locale?: string;
    fallbackLocale?: string;
    translations?: Record<string, unknown>;
};

const convertPlaceholders = (value: unknown): unknown => {
    if (Array.isArray(value)) {
        return value.map((item) => convertPlaceholders(item));
    }

    if (value && typeof value === 'object') {
        const converted: Record<string, unknown> = {};
        Object.entries(value).forEach(([key, item]) => {
            converted[key] = convertPlaceholders(item);
        });
        return converted;
    }

    if (typeof value === 'string') {
        return value.replace(/(^|[^A-Za-z0-9_]):([A-Za-z0-9_]+)/g, '$1{$2}');
    }

    return value;
};

export const buildI18n = (pageProps: I18nPageProps) => {
    const locale = pageProps.locale ?? 'en';
    const fallbackLocale = pageProps.fallbackLocale ?? 'en';
    const messages = convertPlaceholders(pageProps.translations ?? {}) as Record<string, unknown>;

    return createI18n({
        legacy: false,
        globalInjection: true,
        locale,
        fallbackLocale,
        messages: { [locale]: messages },
        missingWarn: false,
        fallbackWarn: false,
    });
};

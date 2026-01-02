import { createI18n } from 'vue-i18n';

export type I18nPageProps = {
    locale?: string;
    fallbackLocale?: string;
    translations?: Record<string, unknown>;
};

export const buildI18n = (pageProps: I18nPageProps) => {
    const locale = pageProps.locale ?? 'en';
    const fallbackLocale = pageProps.fallbackLocale ?? 'en';
    const messages = pageProps.translations ?? {};

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

{{-- Translations for JavaScript: __('Texto en español', { name: 'x' }) returns the text in the current language.
     Keys are the Spanish texts (lang/en.json has the English ones), placeholders are ":name". --}}
@php
    $jsLocale = app()->getLocale();
    $jsLines = json_decode((string) @file_get_contents(lang_path($jsLocale . '.json')), true) ?: [];
    // Only the texts that change (Spanish keys mostly map to themselves)
    $jsLines = array_filter($jsLines, fn ($v, $k) => $v !== $k, ARRAY_FILTER_USE_BOTH);
@endphp
<script>
    window.APP_LOCALE = @json($jsLocale);
    window.I18N = @json((object) $jsLines);
    // Locale for toLocaleDateString / Intl
    window.DATE_LOCALE = window.APP_LOCALE === 'en' ? 'en-US' : 'es-US';

    function __(text, params) {
        let s = Object.prototype.hasOwnProperty.call(window.I18N, text) ? window.I18N[text] : text;
        if (params) {
            // Longest names first so ":name" doesn't break ":name_full"
            Object.keys(params).sort((a, b) => b.length - a.length)
                .forEach(k => { s = s.split(':' + k).join(params[k] ?? ''); });
        }
        return s;
    }

    // ES / EN switch: the page reloads in the other language (the open tab is kept)
    function setLanguage(locale) {
        try { if (window.currentTab) sessionStorage.setItem('nyds-tab', window.currentTab); } catch (e) {}
        window.location.href = @json(url('language')) + '/' + locale;
    }
</script>

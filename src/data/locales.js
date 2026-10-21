/**
 * Country and locale lists for the registration form. In both lists the US
 * and UK entries are pinned at the top, with the rest of the major
 * countries following alphabetically.
 *
 * The form defaults to the first entry in each list (US / en-US) - this is
 * deliberate, see the project README and the test grid's "locale and
 * country selection" row.
 */

export const COUNTRIES = [
  { code: 'US', name: 'United States' },
  { code: 'GB', name: 'United Kingdom' },
  { code: 'AU', name: 'Australia' },
  { code: 'BR', name: 'Brazil' },
  { code: 'CA', name: 'Canada' },
  { code: 'CN', name: 'China' },
  { code: 'FR', name: 'France' },
  { code: 'DE', name: 'Germany' },
  { code: 'IN', name: 'India' },
  { code: 'IE', name: 'Ireland' },
  { code: 'IT', name: 'Italy' },
  { code: 'JP', name: 'Japan' },
  { code: 'MX', name: 'Mexico' },
  { code: 'NL', name: 'Netherlands' },
  { code: 'NZ', name: 'New Zealand' },
  { code: 'PT', name: 'Portugal' },
  { code: 'ES', name: 'Spain' },
  { code: 'ZA', name: 'South Africa' },
];

export const LOCALES = [
  { code: 'en-US', label: 'English (United States)' },
  { code: 'en-GB', label: 'English (United Kingdom)' },
  { code: 'en-AU', label: 'English (Australia)' },
  { code: 'pt-BR', label: 'Portuguese (Brazil)' },
  { code: 'en-CA', label: 'English (Canada)' },
  { code: 'zh-CN', label: 'Chinese (China)' },
  { code: 'fr-FR', label: 'French (France)' },
  { code: 'de-DE', label: 'German (Germany)' },
  { code: 'hi-IN', label: 'Hindi (India)' },
  { code: 'en-IE', label: 'English (Ireland)' },
  { code: 'it-IT', label: 'Italian (Italy)' },
  { code: 'ja-JP', label: 'Japanese (Japan)' },
  { code: 'es-MX', label: 'Spanish (Mexico)' },
  { code: 'nl-NL', label: 'Dutch (Netherlands)' },
  { code: 'en-NZ', label: 'English (New Zealand)' },
  { code: 'pt-PT', label: 'Portuguese (Portugal)' },
  { code: 'es-ES', label: 'Spanish (Spain)' },
  { code: 'en-ZA', label: 'English (South Africa)' },
];

export const DEFAULT_COUNTRY = COUNTRIES[0].code; // 'US'
export const DEFAULT_LOCALE = LOCALES[0].code; // 'en-US'

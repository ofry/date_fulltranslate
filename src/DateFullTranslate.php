<?php
    /**
     * Created by PhpStorm.
     * User: ofryak
     * Date: 15.08.18
     * Time: 13:13
     */

    namespace ofry\DateFullTranslate;

    use Jenssegers\Date\Date;
    use Carbon\Carbon;
    use DateTimeZone;

    class DateFullTranslate extends Date
    {
        /**
         * Create a Date instance from a string.
         *
         * @param  string $time
         * @param  string|DateTimeZone $timezone
         * @return DateFullTranslate
         */
        public static function parse($time = null, $timezone = null)
        {
            if ($time instanceof Carbon) {
                return new static(
                    $time->toDateTimeString(),
                    $timezone ?: $time->getTimezone()
                );
            }

            if (!is_int($time)) {
                $time = static::translateTimeString($time);
            }

            return new static($time, $timezone);
        }

        /**
         * Create a DateFullTranslate instance from a specific format.
         *
         * @param string                    $format Datetime format
         * @param string                    $time
         *
         */
        public static function createFromFormat($format, $time, $timezone = null)
        {
            $time = static::translateTimeString($time);

            return parent::createFromFormat($format, $time, $timezone);
        }

        /**
         * Translate a locale based time string to its english equivalent.
         *
         * @param  string $time
         * @return string
         */
        public static function translateTimeString($time)
        {
            // Don't run translations for english.
            if (static::getLocale() === 'en') {
                return $time;
            }

            // Get all the language lines of the current locale.
            $all = static::getTranslator()->getCatalogue()->all();
            $terms = $all['messages'];

            // Split terms with a | sign.
            foreach ($terms as $i => $term) {
                if (strpos($term, '|') === false) {
                    continue;
                }

                // Split term options.
                $options = explode('|', $term);

                // Remove :count and {count} placeholders.
                $options = array_map(function ($option) {
                    $option = trim(str_replace(':count', '', $option));
                    $option = preg_replace('/({\d+(,(\d+|Inf))?}|\[\d+(,(\d+|Inf))?\])/', '', $option);

                    return $option;
                }, $options);

                $terms[$i] = $options;
            }

            // Replace the localized words with English words.
            $translated = $time;
            foreach ($terms as $english => $localized) {
                $translated = str_ireplace($localized, $english, $translated);
            }

            return $translated;
        }
    }
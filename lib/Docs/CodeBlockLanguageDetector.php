<?php

declare(strict_types=1);

namespace Doctrine\Website\Docs;

use function trim;

class CodeBlockLanguageDetector
{
    /**
     * We use some language aliases not supported by our highlighter library
     * so we manage a mapping layer here.
     */
    private const ALIASES = [
        'html+php' => 'php',
        'html+jinja' => 'html',
        'php-annotations' => 'php',
    ];

    /**
     * @param string[] $lines
     */
    public function detectLanguage(string $language, array $lines) : string
    {
        $language = trim($language);

        if ($language !== '' && isset(self::ALIASES[$language])) {
            return self::ALIASES[$language];
        }

        // detect the language if it does not exist
        // it should exist but our documentation doesn't always specify the language
        if ($language === '') {
            $language = 'console';

            if (isset($lines[0])) {
                $firstLine = trim($lines[0]);

                // first line of the code block is an opening php tag
                if ($firstLine === '<?php') {
                    $language = 'php';
                }
            }
        }

        return $language;
    }
}

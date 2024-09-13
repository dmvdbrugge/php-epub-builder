<?php

declare(strict_types=1);

namespace DMvdBrugge\EpubBuilder\Validation\Validators;

use function preg_match;

class IetfValidator extends BaseValidator
{
    /**
     * @see https://en.wikipedia.org/wiki/IETF_language_tag
     *
     * Some of these parts are actually from a list, but that goes too far. The regex is built up as follows:
     *
     *     $primary    = '([a-z]{2,3}|[a-z]{5,8})';
     *     $extended   = '(-[a-z]{3}){0,3}';
     *     $script     = '(-[a-z]{4})?';
     *     $region     = '(-([a-z]{2}|[0-9]{3}))?';
     *     $variant    = '(-([a-z]{5,8}|\d[a-z0-9]{3}))*';
     *     $extension  = '([a-wyz0-9](-[a-z0-9]{2,8})+)';
     *     $extensions = "(-{$extension})*";
     *     $private    = '(-x(-[a-z0-9]{1,8})+)?';
     *
     *     REGEX = '/^' . $primary . $extended . $script . $region . $variant . $extensions . $private . '$/i';
     */
    private const REGEX = '/^([a-z]{2,3}|[a-z]{5,8})(-[a-z]{3}){0,3}(-[a-z]{4})?(-([a-z]{2}|[0-9]{3}))?(-([a-z]{5,8}|\d[a-z0-9]{3}))*(-([a-wyz0-9](-[a-z0-9]{2,8})+))*(-x(-[a-z0-9]{1,8})+)?$/i';

    public function __construct(
        private readonly string $language,
    ) {
    }

    public function valid(): bool
    {
        return preg_match(self::REGEX, $this->language) === 1;
    }

    protected function message(): string
    {
        return "Language '{$this->language}' is not a valid IETF language tag";
    }
}

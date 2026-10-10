<?php

namespace App\Support;

use DOMDocument;
use DOMElement;
use DOMNode;

/**
 * Sanitizer HTML berbasis DOMDocument (tanpa dependency eksternal) untuk
 * konten pengumuman platform. Hanya tag rich-text dasar yang diizinkan;
 * script/iframe/style dan seluruh atribut on* dibuang.
 */
class HtmlSanitizer
{
    /**
     * @var array<string, array<int, string>>
     */
    private const ALLOWED = [
        'p' => [],
        'br' => [],
        'strong' => [],
        'b' => [],
        'em' => [],
        'i' => [],
        'u' => [],
        's' => [],
        'ul' => [],
        'ol' => [],
        'li' => [],
        'h2' => [],
        'h3' => [],
        'h4' => [],
        'blockquote' => [],
        'a' => ['href', 'target', 'rel'],
    ];

    /**
     * @var array<int, string>
     */
    private const DROP_WITH_CONTENT = [
        'script',
        'style',
        'iframe',
        'object',
        'embed',
        'svg',
        'math',
        'template',
        'noscript',
        'form',
        'input',
        'button',
        'link',
        'meta',
        'img',
    ];

    public static function clean(?string $html): string
    {
        if ($html === null || trim($html) === '') {
            return '';
        }

        $html = self::stripControlChars($html);

        $dom = new DOMDocument('1.0', 'UTF-8');
        $previous = libxml_use_internal_errors(true);

        $loaded = $dom->loadHTML(
            '<?xml encoding="UTF-8"><div id="__sanitizer_root__">'.$html.'</div>',
            LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD
        );

        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        if (! $loaded) {
            return e(strip_tags($html));
        }

        $root = null;

        foreach ($dom->childNodes as $node) {
            if ($node instanceof DOMElement) {
                $root = $node;
                break;
            }
        }

        if (! $root) {
            return e(strip_tags($html));
        }

        self::sanitizeChildren($root);

        $output = '';

        foreach ($root->childNodes as $child) {
            $output .= $dom->saveHTML($child);
        }

        return trim($output);
    }

    private static function sanitizeChildren(DOMNode $node): void
    {
        $child = $node->firstChild;

        while ($child !== null) {
            $next = $child->nextSibling;

            if ($child instanceof DOMElement) {
                $tag = strtolower($child->nodeName);

                if (in_array($tag, self::DROP_WITH_CONTENT, true)) {
                    $node->removeChild($child);
                } elseif (! isset(self::ALLOWED[$tag])) {
                    self::sanitizeChildren($child);
                    self::unwrap($child);
                } else {
                    self::cleanAttributes($child, $tag);
                    self::sanitizeChildren($child);
                }
            } elseif ($child->nodeType === XML_COMMENT_NODE || $child->nodeType === XML_PI_NODE) {
                $node->removeChild($child);
            }

            $child = $next;
        }
    }

    private static function unwrap(DOMElement $element): void
    {
        $parent = $element->parentNode;

        if (! $parent) {
            return;
        }

        while ($element->firstChild) {
            $parent->insertBefore($element->firstChild, $element);
        }

        $parent->removeChild($element);
    }

    private static function cleanAttributes(DOMElement $element, string $tag): void
    {
        $allowed = self::ALLOWED[$tag];

        if ($element->hasAttributes()) {
            $remove = [];

            foreach ($element->attributes as $attribute) {
                if (! in_array(strtolower($attribute->nodeName), $allowed, true)) {
                    $remove[] = $attribute->nodeName;
                }
            }

            foreach ($remove as $name) {
                $element->removeAttribute($name);
            }
        }

        if ($tag === 'a') {
            $href = trim((string) $element->getAttribute('href'));

            if ($href === '' || ! self::isSafeUrl($href)) {
                $element->removeAttribute('href');
            }

            if ($element->getAttribute('href') !== '') {
                $element->setAttribute('target', '_blank');
                $element->setAttribute('rel', 'noopener noreferrer');
            }
        }
    }

    private static function isSafeUrl(string $url): bool
    {
        $normalized = strtolower(preg_replace('/\s+/', '', $url) ?? '');

        return str_starts_with($normalized, 'http://')
            || str_starts_with($normalized, 'https://')
            || str_starts_with($normalized, 'mailto:')
            || str_starts_with($normalized, '/');
    }

    private static function stripControlChars(string $html): string
    {
        return preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F]/u', '', $html) ?? $html;
    }
}

<?php

namespace Dontdrinkandroot\GitkiBundle\Repository;

class LogParser
{
    final public const string COMMIT_BEGIN = 'commit_begin';
    final public const string COMMIT_END = 'commit_end';
    final public const string HASH_BEGIN = 'hash_begin';
    final public const string HASH_END = 'hash_end';
    final public const string AUTHOR_BEGIN = 'author_begin';
    final public const string AUTHOR_END = 'author_end';
    final public const string MAIL_BEGIN = 'mail_begin';
    final public const string MAIL_END = 'mail_end';
    final public const string MESSAGE_BEGIN = 'message_begin';
    final public const string MESSAGE_END = 'message_end';
    final public const string DATE_BEGIN = 'date_begin';
    final public const string DATE_END = 'date_end';

    public static function getFormatString(): string
    {
        $s = self::COMMIT_BEGIN;
        $s .= self::HASH_BEGIN . '%H' . self::HASH_END;
        $s .= self::AUTHOR_BEGIN . '%an' . self::AUTHOR_END;
        $s .= self::MAIL_BEGIN . '%ae' . self::MAIL_END;
        $s .= self::DATE_BEGIN . '%ct' . self::DATE_END;
        $s .= self::MESSAGE_BEGIN . '%s' . self::MESSAGE_END;
        $s .= self::COMMIT_END;

        return $s;
    }

    /**
     * @return non-empty-string
     */
    public static function getMatchString(): string
    {
        $s = '/';
        $s .= self::COMMIT_BEGIN;
        $s .= self::HASH_BEGIN . '(.*?)' . self::HASH_END;
        $s .= self::AUTHOR_BEGIN . '(.*?)' . self::AUTHOR_END;
        $s .= self::MAIL_BEGIN . '(.*?)' . self::MAIL_END;
        $s .= self::DATE_BEGIN . '(.*?)' . self::DATE_END;
        $s .= self::MESSAGE_BEGIN . '(.*?)' . self::MESSAGE_END;
        $s .= self::COMMIT_END;
        $s .= '/s';

        return $s;
    }
}

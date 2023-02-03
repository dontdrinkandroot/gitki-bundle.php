<?php

namespace Dontdrinkandroot\GitkiBundle\Repository;

class LogParser
{
    final const COMMIT_BEGIN = 'commit_begin';
    final const COMMIT_END = 'commit_end';
    final const HASH_BEGIN = 'hash_begin';
    final const HASH_END = 'hash_end';
    final const AUTHOR_BEGIN = 'author_begin';
    final const AUTHOR_END = 'author_end';
    final const MAIL_BEGIN = 'mail_begin';
    final const MAIL_END = 'mail_end';
    final const MESSAGE_BEGIN = 'message_begin';
    final const MESSAGE_END = 'message_end';
    final const DATE_BEGIN = 'date_begin';
    final const DATE_END = 'date_end';

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

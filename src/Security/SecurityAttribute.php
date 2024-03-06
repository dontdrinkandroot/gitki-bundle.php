<?php

namespace Dontdrinkandroot\GitkiBundle\Security;

/**
 * Collection of checked Security Attributes
 */
class SecurityAttribute
{
    final public const string READ_HISTORY = 'GITKI_READ_HISTORY';
    final public const string READ_PATH = 'GITKI_READ_PATH';
    final public const string WRITE_PATH = 'GITKI_WRITE_PATH';
}

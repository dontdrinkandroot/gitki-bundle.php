<?php

namespace Dontdrinkandroot\GitkiBundle\Security;

/**
 * Collection of checked Security Attributes
 */
enum Attribute
{
    case READ_HISTORY;
    case READ_PATH;
    case WRITE_PATH;
}

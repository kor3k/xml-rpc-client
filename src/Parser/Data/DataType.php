<?php declare(strict_types=1);

namespace kor3k\XmlRpc\Parser\Data;

enum DataType: string
{
    case ARRAY = 'array';
    case STRUCT = 'struct';
    case BASE64 = 'base64';
    case BOOLEAN = 'boolean';
    case DATETIME = 'dateTime.iso8601';
    case INTEGER = 'int';
    case DOUBLE = 'double';
    case STRING = 'string';
    case NULL = 'nil';

    case INTEGER_4 = 'i4';
    case INTEGER_8 = 'i8'; // \PHP_INT_SIZE >== 8
}

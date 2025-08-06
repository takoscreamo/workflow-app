<?php

namespace App\Domain\Entities;

enum NodeType: string
{
    case EXTRACT_TEXT = 'extract_text';
    case GENERATIVE_AI = 'generative_ai';
    case FORMATTER = 'formatter';
}

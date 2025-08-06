<?php

namespace App;

enum NodeType: string
{
    case EXTRACT_TEXT = 'extract_text';
    case GENERATIVE_AI = 'generative_ai';
    case FORMATTER = 'formatter';
}

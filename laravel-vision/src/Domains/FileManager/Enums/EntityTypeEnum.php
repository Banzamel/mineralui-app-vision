<?php

namespace FileManager\Enums;

/**
 * Type of entry in the file manager - distinguishes a regular file from a directory.
 */
enum EntityTypeEnum: string
{
    /**
     * Regular file on disk.
     */
    case file = 'file';
    /**
     * Directory (folder) containing other files or subdirectories.
     */
    case dir = 'dir';
}

<?php

namespace FileManager\Enums;

/**
 * Available disks where company files can be stored (locally, publicly or in the cloud).
 */
enum StoragesEnum: string
{
    /**
     * Local disk - files kept privately on the server.
     */
    case local = 'local';
    /**
     * Public disk - files accessible via a public link.
     */
    case public = 'public';
    /**
     * Amazon S3 cloud disk.
     */
    case aws = 'aws';
}

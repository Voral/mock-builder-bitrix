<?php

declare(strict_types=1);

use Vasoft\MockBuilder\Mocker\MockFunctions;

function BeginNote(): string
{
    return MockFunctions::executeMocked('BeginNote', []);
}

function EndNote(): string
{
    return MockFunctions::executeMocked('EndNote', []);
}

function GetMessage($name, $aReplace = null): string
{
    return MockFunctions::executeMocked('GetMessage', [$name, $aReplace]);
}

function DeleteDirFiles(string $srcPath, string $dstPath, array $exclude = []): bool
{
    return MockFunctions::executeMocked('DeleteDirFiles', [$srcPath, $dstPath, $exclude]);
}

function CopyDirFiles(
    $path_from,
    $path_to,
    $ReWrite = true,
    $Recursive = false,
    $bDeleteAfterCopy = false,
    $strExclude = '',
): bool {
    return MockFunctions::executeMocked(
        'CopyDirFiles',
        [$path_from, $path_to, $ReWrite, $Recursive, $bDeleteAfterCopy, $strExclude],
    );
}

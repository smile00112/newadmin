#!/usr/bin/env php
<?php

/**
 * Rules Synchronization Check Script
 * 
 * Analyzes git diff to detect when rule files might need updating
 * based on changes to related code files.
 * 
 * Usage:
 *   php .cursor/rules/sync-check.php [--branch=main] [--verbose]
 */

$mappingFile = __DIR__ . '/rules-mapping.json';
$verbose = in_array('--verbose', $argv);
$branch = 'main';

// Parse arguments
foreach ($argv as $arg) {
    if (strpos($arg, '--branch=') === 0) {
        $branch = substr($arg, 9);
    }
}

if (!file_exists($mappingFile)) {
    echo "Error: rules-mapping.json not found\n";
    exit(1);
}

$mapping = json_decode(file_get_contents($mappingFile), true);
if (!$mapping) {
    echo "Error: Invalid JSON in rules-mapping.json\n";
    exit(1);
}

// Get changed files from git
$changedFiles = getChangedFiles($branch);
if (empty($changedFiles)) {
    echo "No changed files detected.\n";
    exit(0);
}

if ($verbose) {
    echo "Changed files:\n";
    foreach ($changedFiles as $file) {
        echo "  - $file\n";
    }
    echo "\n";
}

// Check each rule file
$warnings = [];
foreach ($mapping as $ruleFile => $config) {
    $relatedFilesChanged = [];
    
    foreach ($config['related_files'] as $pattern) {
        $matchedFiles = matchPattern($pattern, $changedFiles);
        $relatedFilesChanged = array_merge($relatedFilesChanged, $matchedFiles);
    }
    
    if (!empty($relatedFilesChanged)) {
        $ruleFilePath = __DIR__ . '/' . $ruleFile;
        $ruleFileChanged = in_array($ruleFilePath, $changedFiles) || 
                          in_array('.cursor/rules/' . $ruleFile, $changedFiles);
        
        if (!$ruleFileChanged) {
            $warnings[] = [
                'rule_file' => $ruleFile,
                'related_files' => array_unique($relatedFilesChanged),
                'tables' => $config['related_tables'] ?? [],
                'sections' => $config['critical_sections'] ?? []
            ];
        }
    }
}

// Output results
if (empty($warnings)) {
    echo "✓ All rule files are synchronized with code changes.\n";
    exit(0);
}

echo "⚠ Warning: The following rule files may need updating:\n\n";

foreach ($warnings as $warning) {
    echo "Rule File: {$warning['rule_file']}\n";
    echo "Related files changed:\n";
    foreach ($warning['related_files'] as $file) {
        echo "  - $file\n";
    }
    
    if (!empty($warning['tables'])) {
        echo "Related tables: " . implode(', ', $warning['tables']) . "\n";
    }
    
    if (!empty($warning['sections'])) {
        echo "Sections to review:\n";
        foreach ($warning['sections'] as $section) {
            echo "  - $section\n";
        }
    }
    
    echo "\n";
    echo "Action: Review and update {$warning['rule_file']} if needed.\n";
    echo "Checklist: See .cursor/rules/UPDATE_CHECKLIST.md\n\n";
}

exit(1);

/**
 * Get changed files from git diff
 */
function getChangedFiles($branch) {
    $command = "git diff --name-only $branch...HEAD 2>&1";
    $output = shell_exec($command);
    
    if ($output === null) {
        // Try staged files if branch comparison fails
        $output = shell_exec("git diff --cached --name-only 2>&1");
    }
    
    if ($output === null) {
        return [];
    }
    
    $files = array_filter(array_map('trim', explode("\n", $output)));
    return array_values($files);
}

/**
 * Match file pattern against file list
 * Supports wildcards: *, **
 */
function matchPattern($pattern, $files) {
    $matched = [];
    
    // Convert pattern to regex
    $regex = str_replace(
        ['/', '*', '.'],
        ['\/', '.*', '\.'],
        $pattern
    );
    
    // Handle ** (matches any directory)
    $regex = str_replace('.*.*', '.*', $regex);
    
    $regex = '/^' . $regex . '$/';
    
    foreach ($files as $file) {
        // Normalize path separators
        $normalizedFile = str_replace('\\', '/', $file);
        
        if (preg_match($regex, $normalizedFile)) {
            $matched[] = $file;
        }
    }
    
    return $matched;
}

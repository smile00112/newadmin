# Cursor Rules Documentation

This directory contains rule files that help AI assistants understand project-specific architecture, conventions, and domain knowledge.

## Overview

Rule files are Markdown documents (`.mdc`) that provide context and specifications for different aspects of the project. They are automatically loaded by Cursor's AI based on the context of your requests.

## File Structure

- **`context-rules-loader.mdc`** - Main router file that determines when to load specialized rule files
- **`cart-rules-coupons.mdc`** - Specification for the discount coupon system
- **`rules-mapping.json`** - Maps code files to rule files for synchronization tracking
- **`UPDATE_CHECKLIST.md`** - Checklist for updating rules when code changes
- **`sync-check.php`** - Script to detect when rules need updating
- **`README.md`** - This file

## How Rules Work

### Automatic Loading

1. `context-rules-loader.mdc` is always active (`alwaysApply: true`)
2. When you mention topics related to a rule file, the loader automatically includes that rule file
3. The AI uses information from rule files to provide more accurate assistance

### Rule File Structure

Each specialized rule file:
- Has `alwaysApply: false` to avoid context overload
- Contains frontmatter with `description` and `keywords`
- Includes comprehensive documentation about a specific domain

## Rules Synchronization System

### Problem

When code changes (database schema, API endpoints, business logic), rule files can become outdated, leading to incorrect AI assistance.

### Solution

A semi-automatic synchronization system that:
- Tracks which code files relate to which rule files
- Detects changes in related code files
- Reminds developers to update rule files when relevant changes occur
- Provides guidance on what needs to be updated

### How It Works

#### 1. Rules Mapping (`rules-mapping.json`)

Maps code file patterns to rule files:

```json
{
  "cart-rules-coupons.mdc": {
    "related_files": [
      "packages/Webkul/CartRule/src/Models/*.php",
      "packages/Webkul/CartRule/src/Repositories/*.php"
    ],
    "related_tables": ["cart_rules", "cart_rule_coupons"],
    "critical_sections": ["Database Structure", "API endpoints"]
  }
}
```

#### 2. Sync Check Script (`sync-check.php`)

Analyzes git changes to detect when rules might need updating:

```bash
php .cursor/rules/sync-check.php
```

Options:
- `--branch=main` - Compare against specific branch (default: main)
- `--verbose` - Show detailed output

#### 3. Update Checklist (`UPDATE_CHECKLIST.md`)

Provides a checklist of what to update when code changes.

#### 4. Git Hook (Optional)

Pre-commit hook can warn when related files change but rule files don't.

### Workflow

#### During Development

1. Developer modifies code files
2. AI assistant detects changes in related files (via `rules-mapping.json`)
3. AI reminds: "You've modified files related to cart-rules-coupons.mdc. Consider updating the rule file."

#### Before Commit

1. Pre-commit hook checks changed files
2. If related files changed but rule file didn't → warning
3. Developer can update rules or skip (with reason)

#### Manual Check

```bash
php .cursor/rules/sync-check.php
```

Gets a report of potentially outdated rule files.

## Adding New Rule Files

### Step 1: Create Rule File

Create a new `.mdc` file in `.cursor/rules/`:

```markdown
---
alwaysApply: false
description: "Brief description"
keywords: ["keyword1", "keyword2"]
---

# Your Rule File Title

[Content...]
```

### Step 2: Add to Context Loader

Add a section to `context-rules-loader.mdc`:

```markdown
### Your Topic

**When to load:**
- Keywords and topics that trigger loading

**File to load:**
`.cursor/rules/your-rule-file.mdc`
```

### Step 3: Add to Rules Mapping

Add entry to `rules-mapping.json`:

```json
{
  "your-rule-file.mdc": {
    "related_files": [
      "path/to/related/files/*.php"
    ],
    "related_tables": ["table_name"],
    "critical_sections": ["Section Name"]
  }
}
```

## Best Practices

### Writing Rule Files

1. **Be Comprehensive**: Include all relevant information about the domain
2. **Keep Updated**: Update rules when code changes
3. **Use English**: Reduces token costs (no translation needed)
4. **Include Examples**: Code examples help AI understand usage
5. **Document Structure**: Explain database schema, API endpoints, classes

### Maintaining Rules

1. **Update Immediately**: Don't delay updating rules after code changes
2. **Use Checklist**: Follow `UPDATE_CHECKLIST.md` when updating
3. **Run Sync Check**: Regularly run `sync-check.php` to verify synchronization
4. **Review in PRs**: Check rule files during code review

### Synchronization

1. **Check Mapping**: Ensure `rules-mapping.json` includes all related files
2. **Update Mapping**: Add new files/paths when extending functionality
3. **Verify Sections**: Ensure critical sections match actual code structure
4. **Test Examples**: Verify code examples in rules still work

## Troubleshooting

### Rules Not Loading

- Check `context-rules-loader.mdc` has correct keywords
- Verify rule file has proper frontmatter
- Ensure keywords match your request

### Rules Out of Sync

- Run `sync-check.php` to identify issues
- Check `rules-mapping.json` includes all related files
- Review `UPDATE_CHECKLIST.md` for what to update

### False Positives in Sync Check

- Update `rules-mapping.json` to refine file patterns
- Some changes might not require rule updates (document exceptions)

## Files Reference

- **`context-rules-loader.mdc`** - Router for loading rules
- **`rules-mapping.json`** - Code-to-rules mapping
- **`UPDATE_CHECKLIST.md`** - Update checklist template
- **`sync-check.php`** - Synchronization detection script
- **`*.mdc`** - Specialized rule files

## Contributing

When adding or modifying rules:

1. Follow the structure of existing rule files
2. Update `rules-mapping.json` if adding new rule files
3. Update `context-rules-loader.mdc` if adding new topics
4. Test that rules load correctly
5. Verify synchronization works

## Support

For questions or issues:
- Check this README
- Review `UPDATE_CHECKLIST.md`
- Run `sync-check.php` for diagnostics
- Check `rules-mapping.json` for file mappings

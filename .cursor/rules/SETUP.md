# Rules Synchronization System - Setup Guide

## Quick Setup

The rules synchronization system is now installed. Follow these steps to complete setup:

### 1. Git Hook Setup (Optional but Recommended)

The pre-commit hook is located at `.git/hooks/pre-commit`. 

**On Linux/Mac:**
```bash
chmod +x .git/hooks/pre-commit
```

**On Windows:**
The hook should work as-is. If you're using Git Bash, you can make it executable:
```bash
chmod +x .git/hooks/pre-commit
```

**Note:** The hook currently only warns and doesn't block commits. To enable blocking, edit `.git/hooks/pre-commit` and uncomment the `exit 1` line.

### 2. Test the Sync Check Script

Run the sync check script to verify it works:

```bash
php .cursor/rules/sync-check.php
```

Or with verbose output:

```bash
php .cursor/rules/sync-check.php --verbose
```

### 3. Verify Rules Mapping

Check that `rules-mapping.json` includes all relevant files for your rule files:

```bash
cat .cursor/rules/rules-mapping.json
```

Add new entries when creating new rule files.

## Usage

### During Development

When you modify code files:
1. AI assistant will remind you if related rule files need updating
2. Check `UPDATE_CHECKLIST.md` for what to update
3. Update the rule file before committing

### Before Committing

The pre-commit hook will:
1. Check if code files related to rules were changed
2. Warn if rule files weren't updated
3. Allow you to proceed or update rules first

### Manual Check

Run sync check anytime:

```bash
php .cursor/rules/sync-check.php
```

## Configuration

### Enable Commit Blocking

To block commits when rules are out of sync, edit `.git/hooks/pre-commit`:

Find this line:
```bash
# exit 1
```

Uncomment it:
```bash
exit 1
```

### Customize Branch Comparison

Default compares against `main` branch. To change:

```bash
php .cursor/rules/sync-check.php --branch=develop
```

### Add New Rule Files

1. Create rule file in `.cursor/rules/`
2. Add entry to `rules-mapping.json`
3. Add section to `context-rules-loader.mdc`
4. Test that sync check detects it

## Troubleshooting

### Hook Not Running

- Verify hook file exists: `.git/hooks/pre-commit`
- Check file permissions (Linux/Mac): `chmod +x .git/hooks/pre-commit`
- Ensure Git hooks are enabled: `git config core.hooksPath .git/hooks`

### Sync Check Not Working

- Verify PHP is available: `php --version`
- Check script is executable: `chmod +x .cursor/rules/sync-check.php`
- Verify `rules-mapping.json` is valid JSON

### False Positives

- Update `rules-mapping.json` to refine file patterns
- Some changes might not require rule updates (document in comments)

## Next Steps

1. Test the system with a code change
2. Verify sync check detects changes correctly
3. Update rules when needed
4. Consider enabling commit blocking if team is disciplined

## Support

See `.cursor/rules/README.md` for detailed documentation.

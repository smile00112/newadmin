# Rules Update Checklist

When modifying code related to rules, use this checklist to ensure rule files stay synchronized with code changes.

## General Guidelines

- Update rule files **before** committing changes to related code
- Review rule files during code review if related code was modified
- Run `sync-check.php` script to verify synchronization

## Checklist Items

### Database Schema Changes
- [ ] New table created → Add to "Database Structure" section
- [ ] Table modified (new columns, changed types) → Update table schema in rules
- [ ] Migration file changed → Verify table structure matches rules
- [ ] Related tables: `cart_rules`, `cart_rule_coupons`, `cart_rule_coupon_usage`, etc.

### API Endpoints Changes
- [ ] New endpoint added → Add to "API for Working with..." section
- [ ] Endpoint modified (parameters, response format) → Update endpoint documentation
- [ ] Endpoint removed → Remove from rules
- [ ] Check both Shop API and Admin API sections

### Business Logic Changes
- [ ] Validation logic changed → Update "Coupon Application Logic" section
- [ ] New discount type added → Add to "Discount Types" section
- [ ] Calculation formulas changed → Update formulas in rules
- [ ] New conditions added → Update "Application Conditions" section

### Classes and Methods
- [ ] New class added → Add to "Main Classes and Files" section
- [ ] Class renamed → Update class references
- [ ] Method signature changed → Update usage examples
- [ ] New repository method → Update examples if public API

### Important Points
- [ ] Validation flow changed → Update "Important Points" section
- [ ] New edge cases discovered → Add to important points
- [ ] Best practices updated → Update relevant sections

### Usage Examples
- [ ] New feature added → Add example to "Usage Examples" section
- [ ] API usage changed → Update code examples
- [ ] Common patterns changed → Update examples

## Quick Reference

| Code Change | Rule Section to Update |
|------------|----------------------|
| Migration file | Database Structure |
| Controller method | API endpoints |
| Helper/Service logic | Application Logic |
| Model changes | Database Structure, Main Classes |
| Repository changes | Main Classes, Usage Examples |
| Validation changes | Application Logic, Important Points |

## After Updating Rules

1. Verify all related sections are updated
2. Check that examples still work with new code
3. Ensure keywords in frontmatter are still relevant
4. Update `rules-mapping.json` if new files/paths need tracking

## Need Help?

- See `.cursor/rules/README.md` for detailed documentation
- Check `rules-mapping.json` to see which files are tracked
- Run `php .cursor/rules/sync-check.php` to detect outdated rules

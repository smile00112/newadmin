# Alfabank Payment

## Refund flow (refund.do)

The package supports refund request dispatch to Alfabank API during the standard Sales refund flow.

- Trigger point: `sales.refund.save.before`
- Supported mode: amount-based refund (`orderId` + `amount`)
- Minimal payload: `userName`, `password/token`, `orderId`, `amount`
- Optional payload: `language`, `currency`
- Current limitation: no `refundItems`, no idempotency parameters (`expectedDepositedAmount`, `externalRefundId`)

### Smoke checklist (staging)

1. Create and fully pay an order with payment method `alfabank`.
2. In Admin, create a refund for the order in Sales -> Refunds.
3. Confirm no local refund is saved when gateway returns `errorCode != 0`.
4. Confirm refund is saved when gateway returns `errorCode = 0`.
5. Verify final operation status via `getOrderStatusExtended.do`.

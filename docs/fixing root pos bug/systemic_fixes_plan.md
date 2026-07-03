# Implementation Plan - POS Limit Systemic Root Cause Fixes

This plan addresses the systemic issues causing incorrect POS limit deductions for members. It moves beyond one-off data fixes to permanent code improvements.

## User Review Required

> [!IMPORTANT]
> **Logic Change**: The calculation of "limit used" will now strictly use the `angsuran` (monthly installment) value stored in the `penjualan` table rather than picking values from the `angsuran_belanja` table. This ensures the limit is always deducted by the correct monthly amount, even if individual installment records have data quirks.

> [!WARNING]
> **Database Modification**: I will add three missing columns to the `penjualan` table (`alasan_batal`, `dibatalkan_oleh`, `tanggal_batal`). These are required by the existing logic in the controller but are missing from the current production schema, which would cause crashes if a transaction is canceled.

## Proposed Changes

### 1. POS Controller Fixes

#### [MODIFY] [PenjualanBaruController.php](file:///e:/Vibe/Esimko/app/Http/Controllers/PenjualanBaruController.php)

- **Fix Ghost Prevention**: Add an `else` block to the payment method update logic. When a user switches from Credit (ID 3) to any other method, all existing records in `angsuran_belanja` for that sale will be deleted.
- **Fix Data Integrity**: Ensure `tenor` and `angsuran` are correctly initialized or set to default values when the payment method is updated, preventing NULL values that lead to full-amount deductions.

### 2. Global Helper Standardisation

#### [MODIFY] [GlobalHelper.php](file:///e:/Vibe/Esimko/app/Helpers/GlobalHelper.php)

- **Robust Limit Calculation**: Rewrite `limitKaryawan` to:
    1. Identify all active credit sales (those with at least one pending installment record).
    2. Sum the `angsuran` column from those identified `penjualan` records.
    3. Subtract this sum from the 1.5M limit.
- **Safety**: Add `COALESCE` or default value handling to treat missing `angsuran` data as a full deduction (safest approach) while logging the issue.

### 3. Database Schema Update

#### [SQL] `penjualan` Table

Add missing columns identified during the IIS migration audit:
```sql
ALTER TABLE penjualan 
  ADD COLUMN alasan_batal TEXT NULL,
  ADD COLUMN dibatalkan_oleh VARCHAR(200) NULL,
  ADD COLUMN tanggal_batal DATETIME NULL;
```

### 4. Mass Data Cleanup

#### [SQL] `angsuran_belanja` Table

Delete the ~213 confirmed "ghost installments" where the installment is marked as "pending" but the parent sale is not a credit sale or has been paid in other ways.

## Verification Plan

### Automated/Manual Verification
1. **Ghost Prevention Test**: Create a credit sale in POS, then change to Cash. Verify `angsuran_belanja` for that ID is deleted.
2. **Limit Consistency Check**: Manually calculate the limit for K 1667, K 1454, and K 0531 after the fix and compare with the app display.
3. **Column Verification**: Attempt to "Cancel" a transaction in POS to ensure the missing columns fix prevents server errors.

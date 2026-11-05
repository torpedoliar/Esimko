<?php

namespace Tests\Unit;

use App\Support\LoanApplicationRules;
use App\Http\Controllers\MobileController;
use Illuminate\Http\Request;
use PHPUnit\Framework\TestCase;

class LoanApplicationRulesTest extends TestCase
{
    public function test_new_application_tenor_limits()
    {
        foreach ([9 => 60, 10 => 15, 11 => 20] as $type => $max) {
            $this->assertNull(LoanApplicationRules::tenorError($type, $max));
            $this->assertNotNull(LoanApplicationRules::tenorError($type, $max + 1));
        }
    }

    public function test_mobile_rejects_invalid_tenor_before_other_eligibility_checks()
    {
        $controller = new MobileController;
        foreach ([[9, 61], [10, 16], [11, 21], [9, 0], [9, '1.5'], [8, 1]] as $case) {
            $request = new Request(['jenis_pinjaman' => $case[0], 'tenor' => $case[1], 'action' => 'add']);
            $this->assertNotSame('success', $controller->validasi_transaksi($request, 'pinjaman'));
        }
    }

    public function test_invalid_input_and_existing_tenor()
    {
        foreach ([null, '', 0, -1, '1.5', 'abc', []] as $tenor) {
            $this->assertNotNull(LoanApplicationRules::tenorError(9, $tenor));
        }
        foreach ([null, '', 8, 12, '9foo', []] as $type) {
            $this->assertNotNull(LoanApplicationRules::tenorError($type, 1));
        }
        $this->assertNull(LoanApplicationRules::tenorError(10, 18, true));
        $this->assertNotNull(LoanApplicationRules::tenorError(10, 18));
    }
}

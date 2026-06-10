<?php

namespace App\Http\Controllers;

use App\Models\DestructionCertificate;

class DestructionCertificateController extends Controller
{
    public function show(DestructionCertificate $certificate)
    {
        $this->authorize('view', $certificate->berkas);

        $certificate->load(['berkas.patient', 'approver']);

        return view('certificates.destruction', compact('certificate'));
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Patient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PatientController extends Controller
{
    public function index()
    {
    }

    public function patient_details($patient_id)
    {
        $patientDetails = Patient::select(
            'external_patient_id',
            'patients.external_patient_id as patient_id',
            DB::raw(" DATE_FORMAT(patients.created_at, '%Y-%m-%d') AS patient_created_date")
        )
            ->with(["invoice" => function ($query) {
                $query->select(
                    'external_patient_id',
                    'invoice_no',
                    'date'
                );
            }])
            ->with(["receipt" => function ($query) {
                $query->select(
                    'external_patient_id',
                    'receipt_id',
                    'amount',
                    'receipt_date'
                );
            }])
            ->with(["appointment" => function ($query) {
                $query->select(
                    'external_patient_id',
                    'appointment_id',
                    DB::raw(" DATE_FORMAT(appointment_date, '%Y-%m-%d') AS appointment_date")
                )
                    ->orderBy('id', 'ASC')
                    ->limit(1);
            }])

            ->where('patients.external_patient_id', $patient_id)
            ->first();


        $patientDetailArr = [];

        if (!empty($patientDetails)) {

            $patientDetailArr['patient_id'] = $patientDetails->patient_id;

            $first_appointment_id = null;
            $first_appointment_date = null;

            if (isset($patientDetails['appointment'])) {

                if (!empty($patientDetails['appointment'])) {
                    foreach ($patientDetails['appointment'] as $appointment) {
                        $first_appointment_id = $appointment->appointment_id;
                        $first_appointment_date = $appointment->appointment_date;
                    }
                }
            }

            $patientDetailArr['first_appointment_id'] = $first_appointment_id;

            $invoiceArr = [];
            $first_invoice_date = null;

            if (isset($patientDetails['invoice'])) {

                $invoiceDateArr = [];

                if (!empty($patientDetails['invoice'])) {
                    foreach ($patientDetails['invoice'] as $invoice) {
                        array_push($invoiceArr, $invoice->invoice_no);
                        array_push($invoiceDateArr, strtotime($invoice->date));
                    }
                }

                $invoiceDate = min($invoiceDateArr);
                $first_invoice_date = date('Y-m-d', $invoiceDate);
            }

            $patientDetailArr['invoice'] = $invoiceArr;

            $receiptArr = [];
            $totalReceipt = 0;
            $first_receipt_date = null;

            if (isset($patientDetails['receipt'])) {

                $receiptDateArr = [];

                if (!empty($patientDetails['receipt'])) {
                    foreach ($patientDetails['receipt'] as $receipt) {

                        array_push($receiptArr, $receipt->receipt_id);
                        $totalReceipt += $receipt->amount;
                        array_push($receiptDateArr, strtotime($receipt->receipt_date));
                    }
                }

                $receiptDate = min($receiptDateArr);
                $first_receipt_date = date('Y-m-d', $receiptDate);
            }

            $patientDetailArr['total_receipt'] = $totalReceipt;

            $patientDetailArr['receipt'] = $receiptArr;

            $patientDetailArr['first_receipt_date'] = $first_receipt_date;

            $patientDetailArr['first_invoice_date'] = $first_invoice_date;

            $patientDetailArr['first_appointment_date'] = $first_appointment_date;

            $patientDetailArr['patient_created_date'] = $patientDetails->patient_created_date;

            return response()->json($patientDetailArr, 200);

        } else {

            $response = [
                'message' => "No Record Found!"
            ];

            return response()->json($response);
        }
    }
}

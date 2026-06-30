<?php

namespace App\Exports;

use App\Models\BidSchedule;
use App\Models\Procurement;
use App\Models\PrSvp;
use App\Models\Supply;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ProcurementStatusExport implements FromCollection, WithCustomStartCell, WithMapping, WithEvents, WithStyles
{
    protected string $search;
    protected string $startDate;
    protected string $endDate;
    protected int $year;
    protected int $quarter;
    protected string $pmoEndUserFilter;
    protected string $sourceOfFundsFilter;
    protected string $categoryFilter;

    private const LAST_COL = 'W';
    private const LAST_COL_IDX = 23;
    private const NUM_FMT = '#,##0.00';

    private int $currentRow = 6;
    private array $sectionHeaderRows = [];
    private array $totalRows = [];

    private float $completedAbcTotal = 0.0;
    private float $completedContractTotal = 0.0;
    private float $ongoingAbcTotal = 0.0;

    public function __construct(
        string $search,
        string $startDate,
        string $endDate,
        int $year,
        int $quarter,
        string $pmoEndUserFilter = '',
        string $sourceOfFundsFilter = '',
        string $categoryFilter = ''
    ) {
        $this->search = $search;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->year = $year;
        $this->quarter = $quarter;
        $this->pmoEndUserFilter = $pmoEndUserFilter;
        $this->sourceOfFundsFilter = $sourceOfFundsFilter;
        $this->categoryFilter = $categoryFilter;
    }

    public function startCell(): string
    {
        return 'A6';
    }

    // -------------------------------------------------------------------------
    // Collection
    // -------------------------------------------------------------------------

    public function collection()
    {
        $baseWith = [
            'category',
            'clusterCommittee',
            'fundSource',
            'currentPrStage',
            'mopLots.modeOfProcurement',
            'pr_items.prstage',
            'pr_items.mopItems.modeOfProcurement',
            'pr_items.postProcurement.pmu.pmuPos',
            'postProcurement.pmu.pmuPos',
        ];

        $completedQuery = Procurement::query()->with($baseWith)
            ->whereBetween('date_receipt', [$this->startDate, $this->endDate])
            ->where('pr_number', 'like', $this->year . '-%')
            ->where(function ($q) {
                $q->where(fn($s) => $s->where('procurement_type', 'perLot')
                    ->whereHas('prLotPrstages', fn($sq) => $sq->where('pr_stage_id', 7))
                    ->whereHas('postProcurement', fn($sq) => $sq->where('notice_of_award', '<=', $this->endDate)))
                    ->orWhere(fn($s) => $s->where('procurement_type', '!=', 'perLot')
                        ->whereHas('prItemPrstages', fn($sq) => $sq->where('pr_stage_id', 7))
                        ->whereHas('pr_items.postProcurement', fn($sq) => $sq->where('notice_of_award', '<=', $this->endDate)));
            })->latest('date_receipt');
        $this->applyFilters($completedQuery);
        $completed = $completedQuery->get();

        // Mirror the on-screen table (render()): a non-perLot PR is "on-going"
        // if it has at least one item whose LATEST stage is not 7 (covers both
        // pure-ongoing and mixed PRs), so its non-7 items are listed here.
        $ongoingQuery = Procurement::query()->with($baseWith)
            ->whereBetween('date_receipt', [$this->startDate, $this->endDate])
            ->where('pr_number', 'like', $this->year . '-%')
            ->where(function ($q) {
                $q->where(fn($s) => $s->where('procurement_type', 'perLot')
                    ->whereDoesntHave('prLotPrstages', fn($sq) => $sq->where('pr_stage_id', 7)))
                    ->orWhere(fn($s) => $s->where('procurement_type', '!=', 'perLot')
                        ->whereHas('pr_items', fn($sq) => $sq->whereDoesntHave('prstage', fn($st) => $st->where('pr_stage_id', 7))));
            })->latest('date_receipt');
        $this->applyFilters($ongoingQuery);
        $ongoing = $ongoingQuery->get();

        [$cBid, $cSvp, $cSupply] = $this->loadMaps($completed, true);
        [$oBid, $oSvp, $oSupply] = $this->loadMaps($ongoing, false);

        // ── COMPLETED rows — mirror render(): per-perLot one row; for non-perLot
        //    list every item of a PR that has reached stage 7 in its history. ──
        $completedDataRows = collect();
        foreach ($completed as $p) {
            if ($p->procurement_type === 'perLot') {
                $completedDataRows->push($this->buildRow($p, null, $cBid, $cSvp, $cSupply));
            } else {
                foreach ($p->pr_items->filter(
                    fn($i) => $i->procurement?->prItemPrstages?->contains(fn($s) => $s->pr_stage_id == 7)
                ) as $item) {
                    $completedDataRows->push($this->buildRow($p, $item, $cBid, $cSvp, $cSupply));
                }
            }
        }

        // ── ON-GOING rows — mirror render(): per-perLot one row; for non-perLot
        //    list items whose LATEST stage is not 7. ──
        $ongoingDataRows = collect();
        foreach ($ongoing as $p) {
            if ($p->procurement_type === 'perLot') {
                $ongoingDataRows->push($this->buildRow($p, null, $oBid, $oSvp, $oSupply));
            } else {
                foreach ($p->pr_items->filter(fn($i) => ($i->prstage?->pr_stage_id ?? 0) != 7) as $item) {
                    $ongoingDataRows->push($this->buildRow($p, $item, $oBid, $oSvp, $oSupply));
                }
            }
        }

        // Totals computed separately so the summary matches the page exactly
        // (the page's totals are latest-stage based; the listed rows above are not).
        $this->computeTotals($completed);
        $savings = $this->completedAbcTotal - $this->completedContractTotal;

        // Pass 2: assemble
        $rows = collect();

        $rows->push(['__section_header' => 'COMPLETED PROCUREMENT ACTIVITIES']);
        foreach ($completedDataRows as $r)
            $rows->push($r);
        $rows->push(['__total' => ['subtype' => 'abc', 'label' => 'Total Allotted Budget of Procurement Activities', 'value' => $this->completedAbcTotal]]);
        $rows->push(['__total' => ['subtype' => 'contract', 'label' => 'Contract Price of Procurement Activities Conducted', 'value' => $this->completedContractTotal]]);
        $rows->push(['__total' => ['subtype' => 'savings', 'label' => 'Total Savings (Total Allotted Budget - Total Contract Price)', 'value' => $savings]]);

        $rows->push(['__section_header' => 'ON-GOING PROCUREMENT ACTIVITIES']);
        foreach ($ongoingDataRows as $r)
            $rows->push($r);
        $rows->push(['__total' => ['subtype' => 'ongoing_abc', 'label' => 'Total Allotted Budget of On-Going Procurement Activities', 'value' => $this->ongoingAbcTotal]]);

        return $rows;
    }

    // Mirrors ProcurementStatusPage::computeTotals() so the export's summary
    // figures match the page. "Completed" counts a non-perLot item only when its
    // LATEST stage is 7; everything else falls to the on-going budget total.
    private function computeTotals($completed): void
    {
        foreach ($completed as $p) {
            if ($p->procurement_type === 'perLot') {
                $this->completedAbcTotal += (float) ($p->abc ?? 0);
                $this->completedContractTotal += (float) ($p->postProcurement?->awarded_amount ?? 0);
            } else {
                foreach ($p->pr_items as $item) {
                    if ($item->prstage?->pr_stage_id == 7) {
                        $this->completedAbcTotal += (float) ($item->amount ?? 0);
                        $this->completedContractTotal += (float) ($item->postProcurement?->awarded_amount ?? 0);
                    } else {
                        $this->ongoingAbcTotal += (float) ($item->amount ?? 0);
                    }
                }
            }
        }

        $ongoingAll = Procurement::query()->with(['pr_items'])
            ->whereBetween('date_receipt', [$this->startDate, $this->endDate])
            ->where('pr_number', 'like', $this->year . '-%')
            ->where(function ($q) {
                $q->where(fn($s) => $s->where('procurement_type', 'perLot')
                    ->whereDoesntHave('prLotPrstages', fn($sq) => $sq->where('pr_stage_id', 7)))
                    ->orWhere(fn($s) => $s->where('procurement_type', '!=', 'perLot')
                        ->whereDoesntHave('prItemPrstages', fn($sq) => $sq->where('pr_stage_id', 7)));
            });
        $this->applyFilters($ongoingAll);

        foreach ($ongoingAll->get() as $p) {
            if ($p->procurement_type === 'perLot') {
                $this->ongoingAbcTotal += (float) ($p->abc ?? 0);
            } else {
                foreach ($p->pr_items as $item) {
                    $this->ongoingAbcTotal += (float) ($item->amount ?? 0);
                }
            }
        }
    }

    private function applyFilters($query): void
    {
        if (!empty($this->search)) {
            $term = '%' . $this->search . '%';
            $query->where(fn($q) => $q->where('pr_number', 'like', $term)
                ->orWhere('procurement_program_project', 'like', $term));
        }
        if (!empty($this->pmoEndUserFilter))
            $query->whereHas('clusterCommittee', fn($q) => $q->where('clustercommittee', $this->pmoEndUserFilter));
        if (!empty($this->sourceOfFundsFilter))
            $query->whereHas('fundSource', fn($q) => $q->where('fundsources', $this->sourceOfFundsFilter));
        if (!empty($this->categoryFilter))
            $query->whereHas('category', fn($q) => $q->where('category', $this->categoryFilter));
    }

    private function loadMaps($procurements, bool $isCompleted): array
    {
        $allUids = [];

        foreach ($procurements as $p) {
            if ($p->procurement_type === 'perLot') {
                $uid = $p->mopLots->sortByDesc('mode_order')->first()?->uid;
                if ($uid)
                    $allUids[] = $uid;
            } else {
                foreach ($p->pr_items as $item) {
                    $uid = $item->mopItems->sortByDesc('mode_order')->first()?->uid;
                    if ($uid)
                        $allUids[] = $uid;
                }
            }
        }

        $bidMap = collect();
        $svpMap = collect();
        if (!empty($allUids)) {
            $bidMap = BidSchedule::whereIn('mop_uid', $allUids)
                ->get()->keyBy(fn($b) => $b->ref_id . '_' . $b->mop_uid);
            $svpMap = PrSvp::whereIn('mop_uid', $allUids)
                ->get()->keyBy(fn($s) => $s->ref_id . '_' . $s->mop_uid);
        }

        $poNos = [];
        foreach ($procurements as $p) {
            if ($p->procurement_type === 'perLot') {
                foreach ($p->postProcurement?->pmu?->pmuPos ?? [] as $pp)
                    if ($pp->po_contract_number)
                        $poNos[] = $pp->po_contract_number;
            } elseif ($isCompleted) {
                foreach ($p->pr_items->filter(fn($i) => $i->prstage?->pr_stage_id == 7) as $item)
                    foreach ($item->postProcurement?->pmu?->pmuPos ?? [] as $pp)
                        if ($pp->po_contract_number)
                            $poNos[] = $pp->po_contract_number;
            }
        }

        $supplyMap = collect();
        if (!empty($poNos)) {
            $supplyMap = Supply::whereIn('po_contract_number', $poNos)
                ->with('supplyPos')->get()->keyBy('po_contract_number');
        }
        return [$bidMap, $svpMap, $supplyMap];
    }

    // -------------------------------------------------------------------------
    // Mapping
    // -------------------------------------------------------------------------

    public function map($row): array
    {
        $rowNum = $this->currentRow++;

        if (isset($row['__section_header'])) {
            $this->sectionHeaderRows[$rowNum] = $row['__section_header'];
            return array_fill(0, self::LAST_COL_IDX, '');
        }

        if (isset($row['__total'])) {
            $d = $row['__total'];
            $this->totalRows[$rowNum] = $d;
            $arr = array_fill(0, self::LAST_COL_IDX, '');
            $arr[0] = $d['label'];
            // abc / ongoing_abc → col R (17); contract → col U (20); savings → col R (17, will span R:W)
            if (in_array($d['subtype'], ['abc', 'ongoing_abc'])) {
                $arr[17] = (float) $d['value'];
            } elseif ($d['subtype'] === 'contract') {
                $arr[20] = (float) $d['value'];
            } elseif ($d['subtype'] === 'savings') {
                $arr[17] = (float) $d['value'];
            }
            return $arr;
        }

        return $row;
    }

    // -------------------------------------------------------------------------
    // Row builder
    // -------------------------------------------------------------------------

    private function buildRow($procurement, $item, $bidScheduleMap, $prSvpMap, $supplyMap): array
    {
        // "27-NOV-26" format
        $fmt = fn($d) => $d ? strtoupper(\Carbon\Carbon::parse($d)->format('d-M-y')) : '';

        $preProcConf = $adsPostIb = $preBidConf = $eligibility = '';
        $subOpen = $bidEval = $postQual = $modeName = '';

        if ($procurement->procurement_type === 'perLot') {
            $latestMop = $procurement->mopLots->sortByDesc('mode_order')->first();
            $modeName = $latestMop?->modeOfProcurement?->modeofprocurements ?? '';
            $modeId = $latestMop?->mode_of_procurement_id;
            $key = $procurement->procID . '_' . ($latestMop?->uid ?? '');

            $bidSched = in_array($modeId, [2, 3, 4, 5, 6]) ? $bidScheduleMap->get($key) : null;
            $prSvp = in_array($modeId, range(7, 24)) ? $prSvpMap->get($key) : null;

            $preProcConf = $fmt($bidSched?->pre_proc_conference);
            $adsPostIb = $bidSched ? $fmt($bidSched->ads_post_ib) : $fmt($prSvp?->ads_post_ib);
            $preBidConf = $fmt($bidSched?->pre_bid_conf);
            $eligibility = $fmt($bidSched?->eligibility_check);
            $subOpen = $fmt($bidSched?->sub_open_bids);
            $bidEval = $fmt($bidSched?->bid_evaluation_date);
            $postQual = $fmt($bidSched?->post_qualification_date);
        } else {
            $latestMop = $item?->mopItems->sortByDesc('mode_order')->first();
            $modeName = $latestMop?->modeOfProcurement?->modeofprocurements ?? '';
            $modeId = $latestMop?->mode_of_procurement_id;
            $key = ($item?->prItemID ?? '') . '_' . ($latestMop?->uid ?? '');

            $bidSched = in_array($modeId, [2, 3, 4, 5, 6]) ? $bidScheduleMap->get($key) : null;
            $prSvp = in_array($modeId, range(7, 24)) ? $prSvpMap->get($key) : null;

            $preProcConf = $fmt($bidSched?->pre_proc_conference);
            $adsPostIb = $bidSched ? $fmt($bidSched->ads_post_ib) : $fmt($prSvp?->ads_post_ib);
            $preBidConf = $fmt($bidSched?->pre_bid_conf);
            $eligibility = $fmt($bidSched?->eligibility_check);
            $subOpen = $fmt($bidSched?->sub_open_bids);
            $bidEval = $fmt($bidSched?->bid_evaluation_date);
            $postQual = $fmt($bidSched?->post_qualification_date);
        }

        $post = ($item !== null) ? $item->postProcurement : $procurement->postProcurement;
        $pmuPo = $post?->pmu?->pmuPos->first();

        $noticeOfAward = $fmt($post?->notice_of_award);
        $contractSigning = $fmt($pmuPo?->contract_signing_date);
        $noticeToProceed = $fmt($pmuPo?->notice_to_proceed_date);

        $deliveryCompletion = $inspectionAcceptance = '';
        if ($pmuPo?->po_contract_number) {
            $supply = $supplyMap->get($pmuPo->po_contract_number);
            $spo = $supply?->supplyPos->first();
            $deliveryCompletion = $fmt($spo?->delivery_completion);
            $inspectionAcceptance = $fmt($spo?->date_of_acceptance);
        }

        $abcTotal = $item ? ($item->amount ?? '') : ($procurement->abc ?? '');
        $contractTotal = $post?->awarded_amount ?? '';

        $category = $procurement->category?->category ?? '';
        $coCategories = ['IT Peripherals/Equipment', 'Medical Equipment', 'Medical Devices'];
        $isCo = $abcTotal !== '' && (float) $abcTotal >= 50000
            && collect($coCategories)->contains(fn($kw) => stripos($category, $kw) !== false);

        $abcMooe = $abcCo = '';
        if ($abcTotal !== '') {
            $abcCo = $isCo ? (float) $abcTotal : 0.0;
            $abcMooe = $isCo ? 0.0 : (float) $abcTotal;
        }

        $contractMooe = $contractCo = '';
        if ($contractTotal !== '' && $contractTotal !== null) {
            $contractCo = $isCo ? (float) $contractTotal : 0.0;
            $contractMooe = $isCo ? 0.0 : (float) $contractTotal;
        }

        return [
            $procurement->pr_number,
            $item ? ($item->description ?? '') : ($procurement->procurement_program_project ?? ''),
            $procurement->clusterCommittee?->clustercommittee ?? '',
            $modeName,
            $preProcConf,
            $adsPostIb,
            $preBidConf,
            $eligibility,
            $subOpen,
            $bidEval,
            $postQual,
            $noticeOfAward,
            $contractSigning,
            $noticeToProceed,
            $deliveryCompletion,
            $inspectionAcceptance,
            $procurement->fundSource?->fundsources ?? '',
            $abcTotal !== '' ? (float) $abcTotal : '',
            $abcMooe,
            $abcCo,
            $contractTotal !== '' && $contractTotal !== null ? (float) $contractTotal : '',
            $contractMooe,
            $contractCo,
        ];
    }

    // -------------------------------------------------------------------------
    // AfterSheet
    // -------------------------------------------------------------------------

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $quarterName = ['1st', '2nd', '3rd', '4th'][$this->quarter - 1] ?? '';
                $grandTotal = $this->completedAbcTotal + $this->ongoingAbcTotal;
                $pctCompleted = $grandTotal > 0 ? ($this->completedAbcTotal / $grandTotal) * 100 : 0;
                $pctOngoing = $grandTotal > 0 ? ($this->ongoingAbcTotal / $grandTotal) * 100 : 0;

                $thin = ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '000000']];
                $numFmt = ['numberFormat' => ['formatCode' => self::NUM_FMT]];
                $center = ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER];
                $right = ['horizontal' => Alignment::HORIZONTAL_RIGHT, 'vertical' => Alignment::VERTICAL_CENTER];

                // ── Rows 1-3: Title / Year-Quarter / Region ───────────────────
                $sheet->mergeCells('A1:' . self::LAST_COL . '1');
                $sheet->setCellValue('A1', 'Procurement Status Report');
                $sheet->getStyle('A1')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 14],
                    'alignment' => $center,
                ]);
                $sheet->getRowDimension(1)->setRowHeight(24);

                $sheet->mergeCells('A2:' . self::LAST_COL . '2');
                $sheet->setCellValue('A2', 'Year: ' . $this->year . '    Quarter: ' . $quarterName);
                $sheet->getStyle('A2')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 11],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'indent' => 2],
                ]);
                $sheet->getRowDimension(2)->setRowHeight(18);

                $sheet->mergeCells('A3:' . self::LAST_COL . '3');
                $sheet->setCellValue('A3', 'Region/Hospital:    DEPARTMENT OF HEALTH - WESTERN VISAYAS CENTER FOR HEALTH DEVELOPMENT');
                $sheet->getStyle('A3')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 11],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'indent' => 2],
                ]);
                $sheet->getRowDimension(3)->setRowHeight(18);

                // ── Rows 4-5: Column headers ──────────────────────────────────
                foreach (['A', 'B', 'C', 'D', 'Q'] as $col) {
                    $sheet->mergeCells($col . '4:' . $col . '5');
                }
                $sheet->setCellValue('A4', 'Code (PAP)');
                $sheet->setCellValue('B4', 'Procurement Project');
                $sheet->setCellValue('C4', 'PMO/End-User (Cluster)');
                $sheet->setCellValue('D4', 'Mode of Procurement');
                $sheet->setCellValue('Q4', 'Source of Funds');

                $sheet->mergeCells('E4:P4');
                $sheet->setCellValue('E4', 'Actual Procurement Activity');
                $sheet->mergeCells('R4:T4');
                $sheet->setCellValue('R4', 'ABC (PhP)');
                $sheet->mergeCells('U4:W4');
                $sheet->setCellValue('U4', 'Contract Cost (PhP)');

                foreach ([
                    'E5' => 'Pre-Proc Conference',
                    'F5' => 'Ads/Post of IB',
                    'G5' => 'Pre-bid Conf',
                    'H5' => 'Eligibility Check',
                    'I5' => 'Sub/Open of Bids',
                    'J5' => 'Bid Evaluation',
                    'K5' => 'Post Qual',
                    'L5' => 'Notice of Award',
                    'M5' => 'Contract Signing',
                    'N5' => 'Notice to Proceed',
                    'O5' => 'Delivery/Completion',
                    'P5' => 'Inspection & Acceptance',
                    'R5' => 'Total',
                    'S5' => 'MOOE',
                    'T5' => 'CO',
                    'U5' => 'Total',
                    'V5' => 'MOOE',
                    'W5' => 'CO',
                ] as $cell => $value) {
                    $sheet->setCellValue($cell, $value);
                }

                $sheet->getStyle('A4:' . self::LAST_COL . '5')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 10],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
                ]);
                $sheet->getRowDimension(4)->setRowHeight(30);
                $sheet->getRowDimension(5)->setRowHeight(40);

                // ── Collection rows: column-wide styles FIRST ─────────────────
                // (section header & totals overrides applied after so they win)
                $lastRow = $sheet->getHighestRow();

                // WrapText: Procurement Project (B), Mode of Procurement (D), Source of Funds (Q)
                foreach (['B', 'D', 'Q'] as $col) {
                    $sheet->getStyle($col . '6:' . $col . $lastRow)->getAlignment()->setWrapText(true);
                }
                // Center: Code PAP (A), PMO/End-User (C), Mode of Procurement (D), Source of Funds (Q)
                foreach (['A', 'C', 'D', 'Q'] as $col) {
                    $sheet->getStyle($col . '6:' . $col . $lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                }
                // Center dates E:P
                $sheet->getStyle('E6:P' . $lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                // Right-align + number format for financial columns R:W
                $sheet->getStyle('R6:' . self::LAST_COL . $lastRow)->applyFromArray(array_merge(['alignment' => $right], $numFmt));
                // Vertical center for all rows in collection range
                $sheet->getStyle('A6:' . self::LAST_COL . $lastRow)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);

                // ── Section headers (override column-wide) ────────────────────
                foreach ($this->sectionHeaderRows as $r => $text) {
                    $sheet->mergeCells('A' . $r . ':' . self::LAST_COL . $r);
                    $sheet->setCellValue('A' . $r, $text);
                    $sheet->getStyle('A' . $r)->applyFromArray([
                        'font' => ['bold' => true, 'size' => 10],
                        'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => false, 'indent' => 2],
                    ]);
                    $sheet->getRowDimension($r)->setRowHeight(20);
                }

                // ── Totals rows (override column-wide) ────────────────────────
                $labelStyle = ['font' => ['bold' => true, 'size' => 9], 'alignment' => ['horizontal' => Alignment::HORIZONTAL_RIGHT, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => false]];
                $valueStyle = array_merge(['font' => ['bold' => true, 'size' => 9], 'alignment' => $center], $numFmt);

                foreach ($this->totalRows as $r => $d) {
                    $sheet->getRowDimension($r)->setRowHeight(16);
                    $sheet->mergeCells('A' . $r . ':Q' . $r);
                    $sheet->setCellValue('A' . $r, $d['label']);
                    $sheet->getStyle('A' . $r . ':Q' . $r)->applyFromArray($labelStyle);

                    if ($d['subtype'] === 'abc' || $d['subtype'] === 'ongoing_abc') {
                        $sheet->mergeCells('R' . $r . ':T' . $r);
                        $sheet->setCellValue('R' . $r, (float) $d['value']);
                        $sheet->getStyle('R' . $r . ':T' . $r)->applyFromArray($valueStyle);
                        $sheet->mergeCells('U' . $r . ':' . self::LAST_COL . $r);
                    } elseif ($d['subtype'] === 'contract') {
                        $sheet->mergeCells('R' . $r . ':T' . $r);
                        $sheet->mergeCells('U' . $r . ':' . self::LAST_COL . $r);
                        $sheet->setCellValue('U' . $r, (float) $d['value']);
                        $sheet->getStyle('U' . $r . ':' . self::LAST_COL . $r)->applyFromArray($valueStyle);
                    } elseif ($d['subtype'] === 'savings') {
                        // Centered across both ABC + Contract Cost columns
                        $sheet->mergeCells('R' . $r . ':' . self::LAST_COL . $r);
                        $sheet->setCellValue('R' . $r, (float) $d['value']);
                        $sheet->getStyle('R' . $r . ':' . self::LAST_COL . $r)->applyFromArray($valueStyle);
                    }
                }

                // ── Borders: only rows 1-5 and the full collection range ───────
                $sheet->getStyle('A1:' . self::LAST_COL . '5')->applyFromArray(['borders' => ['allBorders' => $thin]]);
                $sheet->getStyle('A6:' . self::LAST_COL . $lastRow)->applyFromArray(['borders' => ['allBorders' => $thin]]);

                // ── SUMMARY TABLE ─────────────────────────────────────────────
                $f = $lastRow + 2;
                $summaryStart = $f;
                $shStyle = ['font' => ['bold' => true, 'size' => 10], 'alignment' => $center];

                $sheet->mergeCells('A' . $f . ':J' . $f);
                $sheet->setCellValue('A' . $f, 'SUMMARY');
                $sheet->getStyle('A' . $f . ':J' . $f)->applyFromArray($shStyle);

                $sheet->mergeCells('K' . $f . ':Q' . $f);
                $sheet->setCellValue('K' . $f, 'ABC (PhP)');
                $sheet->getStyle('K' . $f . ':Q' . $f)->applyFromArray($shStyle);

                $sheet->mergeCells('R' . $f . ':' . self::LAST_COL . $f);
                $sheet->setCellValue('R' . $f, '%');
                $sheet->getStyle('R' . $f . ':' . self::LAST_COL . $f)->applyFromArray($shStyle);
                $sheet->getRowDimension($f)->setRowHeight(18);
                $f++;

                foreach ([
                    ['Completed', $this->completedAbcTotal, $pctCompleted],
                    ['On-Going', $this->ongoingAbcTotal, $pctOngoing],
                ] as [$label, $amount, $pct]) {
                    $sheet->mergeCells('A' . $f . ':J' . $f);
                    $sheet->setCellValue('A' . $f, $label);
                    $sheet->getStyle('A' . $f . ':J' . $f)->applyFromArray(['font' => ['size' => 9], 'alignment' => $center]);

                    $sheet->mergeCells('K' . $f . ':Q' . $f);
                    $sheet->setCellValue('K' . $f, (float) $amount);
                    $sheet->getStyle('K' . $f . ':Q' . $f)->applyFromArray(array_merge(['font' => ['size' => 9], 'alignment' => $right], $numFmt));

                    $sheet->mergeCells('R' . $f . ':' . self::LAST_COL . $f);
                    $sheet->setCellValue('R' . $f, number_format($pct, 2) . '%');
                    $sheet->getStyle('R' . $f . ':' . self::LAST_COL . $f)->applyFromArray(['font' => ['size' => 9], 'alignment' => $right]);
                    $sheet->getRowDimension($f)->setRowHeight(16);
                    $f++;
                }

                $sheet->mergeCells('A' . $f . ':J' . $f);
                $sheet->setCellValue('A' . $f, 'Total');
                $sheet->getStyle('A' . $f . ':J' . $f)->applyFromArray(['font' => ['bold' => true, 'size' => 9], 'alignment' => $center]);

                $sheet->mergeCells('K' . $f . ':Q' . $f);
                $sheet->setCellValue('K' . $f, (float) $grandTotal);
                $sheet->getStyle('K' . $f . ':Q' . $f)->applyFromArray(array_merge(['font' => ['bold' => true, 'size' => 9], 'alignment' => $right], $numFmt));

                $sheet->mergeCells('R' . $f . ':' . self::LAST_COL . $f);
                $sheet->setCellValue('R' . $f, '100.00%');
                $sheet->getStyle('R' . $f . ':' . self::LAST_COL . $f)->applyFromArray(['font' => ['bold' => true, 'size' => 9], 'alignment' => $right]);
                $sheet->getRowDimension($f)->setRowHeight(16);

                // Borders only on summary table rows
                $sheet->getStyle('A' . $summaryStart . ':' . self::LAST_COL . $f)->applyFromArray(['borders' => ['allBorders' => $thin]]);

                $f += 3;

                // ── SIGNATURE BLOCK (no borders) ──────────────────────────────
                $sheet->mergeCells('A' . $f . ':F' . $f);
                $sheet->setCellValue('A' . $f, 'Prepared by:');
                $sheet->getStyle('A' . $f)->applyFromArray(['font' => ['size' => 9], 'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT, 'vertical' => Alignment::VERTICAL_CENTER]]);

                $sheet->mergeCells('R' . $f . ':' . self::LAST_COL . $f);
                $sheet->setCellValue('R' . $f, 'APPROVED:');
                $sheet->getStyle('R' . $f)->applyFromArray(['font' => ['size' => 9], 'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT, 'vertical' => Alignment::VERTICAL_CENTER]]);
                $sheet->getRowDimension($f)->setRowHeight(16);

                $f += 3; // blank rows for signature space
    
                // Signature lines: top border only, no text
                $sheet->mergeCells('A' . $f . ':F' . $f);
                $sheet->getStyle('A' . $f . ':F' . $f)->applyFromArray(['borders' => ['top' => $thin]]);

                $sheet->mergeCells('R' . $f . ':' . self::LAST_COL . $f);
                $sheet->getStyle('R' . $f . ':' . self::LAST_COL . $f)->applyFromArray(['borders' => ['top' => $thin]]);
                $sheet->getRowDimension($f)->setRowHeight(16);

                // ── Freeze & column widths ────────────────────────────────────
                $sheet->freezePane('A6');

                foreach ([
                    'A' => 16,
                    'B' => 36,
                    'C' => 20,
                    'D' => 22,
                    'E' => 14,
                    'F' => 14,
                    'G' => 12,
                    'H' => 14,
                    'I' => 14,
                    'J' => 14,
                    'K' => 12,
                    'L' => 14,
                    'M' => 14,
                    'N' => 14,
                    'O' => 16,
                    'P' => 18,
                    'Q' => 18,
                    'R' => 14,
                    'S' => 14,
                    'T' => 12,
                    'U' => 14,
                    'V' => 14,
                    'W' => 12,
                ] as $col => $width) {
                    $sheet->getColumnDimension($col)->setWidth($width)->setAutoSize(false);
                }
            },
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [];
    }
}

<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Denisok94\SymfonyExportXlsxBundle\Service\XlsxService;
use App\Handlers\Clients\SBIFClient;

#[Route('/rate', name: 'app_rate_')]
final class RateController extends AbstractController
{

    public function __construct(private SBIFClient $sbifClient)
    {}

    #[Route('/', name: 'index')]
    public function index(Request $request): Response
    {
        return $this->render('rate/index.html.twig', [
            'controller_name' => 'RateController',
            ...$this->getDollarDataByRequest($request)
        ]);

    }

    #[Route('/download-xlsx', name: 'download_xlsx')]
    public function downloadXlsx(XlsxService $xlsxService, Request $request): Response
    {
        $data = $this->getDollarDataByRequest($request);
        if (count($data['dolarRates']) == 0) {
            return $this->redirect('/rate/');
        }
        $fileName = "report-dollar-".$data['year'].$data['month'].".xlsx";
        $temp_file = tempnam(sys_get_temp_dir(), $fileName);
        $xlsxService->setFile($temp_file)->open();
        foreach ($data['dolarRates'] as $line) {
            $xlsxService->write($line);
        }
        $xlsxService->close();
        return $this->file($temp_file, $fileName, ResponseHeaderBag::DISPOSITION_INLINE);
    }

    private function getDollarDataByRequest(Request $request): array 
    {
        $year = $request->get('year', null);
        $month = $request->get('month', null);
        $dolarRates = [];
        $isRequestedData = $year !== null && $month !== null;

        if ($isRequestedData) {
            $dolarRates = $this->sbifClient->getDolarRatesByYearAndMonth((int) $year, (int) $month);
        }
        return [
            'year' => $year,
            'month' => $month,
            'dolarRates' => $dolarRates,
            'isRequestedData' => $isRequestedData
        ];
    }
}

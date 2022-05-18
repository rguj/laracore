<?php
namespace app\Exceptions;

use Throwable;
use ErrorException;
use Spatie\Ignition\Ignition as Ignition;
use Spatie\FlareClient\Report;
use Spatie\Ignition\ErrorPage\ErrorPageViewModel;

class IgnitionCustom extends Ignition {

    public function __construct()
    {
        parent::__construct();
    }
    
    final public function renderExceptionCustom(Throwable $throwable, ?Report $report = null): ErrorPageViewModel
    {
        $this->make();

        $this->setUpFlare();

        $report ??= $this->createReport($throwable);

        $viewModel = new ErrorPageViewModel(
            $throwable,
            $this->ignitionConfig,
            $report,
            $this->solutionProviderRepository->getSolutionsForThrowable($throwable),
            $this->solutionTransformerClass,
        );

        return $viewModel;

        // (new Renderer())->render(['viewModel' => $viewModel]);
    }

    final public function getParentClassDirectory()
    {        
        $reflector = new \ReflectionClass(parent::class);
        $fn = $reflector->getFileName();
        return dirname($fn);
    }

    final public function renderViewStringCustom($viewModel)
    {
        // $viewFile = $this->getParentClassDirectory() . '/../../resources/views/errorPage.php';
        $viewFile = rtrim($this->getParentClassDirectory(), '\\src') . '/resources/views/errorPage.php';
        $var = "";

        ob_start();
        extract(['viewModel'=>$viewModel], EXTR_OVERWRITE);
        require_once $viewFile;
        // $var .= ob_get_contents(); 
        $var .= ob_get_clean();
        ob_end_clean();
        return $var;
    }


    final public function renderCustom(array $data)
    {
        // $ignition = new \App\Exceptions\IgnitionCustom();
        // $viewModel = $ignition->renderExceptionCustom(new ErrorException($data['message'], 0, 1, $data['file'], $data['line']));
        // return $ignition->renderViewStringCustom($viewModel);

        $output = '';

        $exception = new ErrorException(
            $data['message'], 
            0, 
            1, 
            $data['file'], 
            $data['line']
        );        
        $viewModel = $this->renderExceptionCustom($exception);
        $output = $this->renderViewStringCustom($viewModel);
        
        try {
        } catch(\Throwable $ex) {            
            // $output = empty($output) ? $this->renderViewStringCustom($viewModel) : '';
        }
        return $output;
    }



}
<?php
namespace app\Exceptions;

use Spatie\Ignition\Ignition as Ignition;
use ArrayObject;
use ErrorException;
use Spatie\FlareClient\Context\BaseContextProviderDetector;
use Spatie\FlareClient\Context\ContextProviderDetector;
use Spatie\FlareClient\Enums\MessageLevels;
use Spatie\FlareClient\Flare;
use Spatie\FlareClient\FlareMiddleware\AddDocumentationLinks;
use Spatie\FlareClient\FlareMiddleware\AddSolutions;
use Spatie\FlareClient\FlareMiddleware\FlareMiddleware;
use Spatie\FlareClient\Report;
use Spatie\Ignition\Config\IgnitionConfig;
use Spatie\Ignition\Contracts\HasSolutionsForThrowable;
use Spatie\Ignition\Contracts\ProvidesSolution;
use Spatie\Ignition\Contracts\SolutionProviderRepository as SolutionProviderRepositoryContract;
use Spatie\Ignition\ErrorPage\ErrorPageViewModel;
use Spatie\Ignition\ErrorPage\Renderer;
use Spatie\Ignition\Solutions\SolutionProviders\BadMethodCallSolutionProvider;
use Spatie\Ignition\Solutions\SolutionProviders\MergeConflictSolutionProvider;
use Spatie\Ignition\Solutions\SolutionProviders\SolutionProviderRepository;
use Spatie\Ignition\Solutions\SolutionProviders\UndefinedPropertySolutionProvider;
use Throwable;

class IgnitionCustom extends Ignition {

    public function __construct()
    {
        parent::__construct();
    }
    
    public function renderExceptionCustom(Throwable $throwable, ?Report $report = null): ErrorPageViewModel
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

    public function getParentClassDirectory()
    {        
        $reflector = new \ReflectionClass(parent::class);
        $fn = $reflector->getFileName();
        return dirname($fn);
    }

    public function renderViewStringCustom($viewModel)
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


    public function renderCustom(array $data)
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
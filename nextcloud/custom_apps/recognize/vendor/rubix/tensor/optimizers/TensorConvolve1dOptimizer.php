<?php

namespace OCA\Recognize\Vendor\Zephir\Optimizers\FunctionCall;

use OCA\Recognize\Vendor\Zephir\Call;
use OCA\Recognize\Vendor\Zephir\CompilationContext;
use OCA\Recognize\Vendor\Zephir\CompiledExpression;
use OCA\Recognize\Vendor\Zephir\HeadersManager;
use OCA\Recognize\Vendor\Zephir\Exception\CompilerException;
use OCA\Recognize\Vendor\Zephir\Optimizers\OptimizerAbstract;
/** @internal */
class TensorConvolve1dOptimizer extends OptimizerAbstract
{
    /**
     * @param mixed[] $expression
     * @param Call $call
     * @param CompilationContext $context
     * @throws CompilerException
     * @return CompiledExpression|bool
     */
    public function optimize(array $expression, Call $call, CompilationContext $context)
    {
        if (!isset($expression['parameters'])) {
            return \false;
        }
        if (\count($expression['parameters']) !== 3) {
            throw new CompilerException('Convolve 1D accepts exactly three arguments, ' . \count($expression['parameters']) . 'given.', $expression);
        }
        $call->processExpectedReturn($context);
        $symbolVariable = $call->getSymbolVariable();
        if (empty($symbolVariable)) {
            throw new CompilerException('Missing symbol variable.');
        }
        if ($symbolVariable->getType() !== 'variable') {
            throw new CompilerException('Return value must only be assigned to a dynamic variable.', $expression);
        }
        if ($call->mustInitSymbolVariable()) {
            $symbolVariable->initVariant($context);
        }
        $context->headersManager->add('include/signal_processing', HeadersManager::POSITION_LAST);
        $resolvedParams = $call->getResolvedParams($expression['parameters'], $context, $expression);
        $symbol = $context->backend->getVariableCode($symbolVariable);
        $context->codePrinter->output("tensor_convolve_1d({$symbol}, {$resolvedParams[0]}, {$resolvedParams[1]}, {$resolvedParams[2]});");
        return new CompiledExpression('variable', $symbolVariable->getRealName(), $expression);
    }
}

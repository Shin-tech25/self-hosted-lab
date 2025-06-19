<?php

namespace OCA\Recognize\Vendor\Zephir\Optimizers\FunctionCall;

use OCA\Recognize\Vendor\Zephir\Call;
use OCA\Recognize\Vendor\Zephir\CompilationContext;
use OCA\Recognize\Vendor\Zephir\CompiledExpression;
use OCA\Recognize\Vendor\Zephir\HeadersManager;
use OCA\Recognize\Vendor\Zephir\Exception\CompilerException;
use OCA\Recognize\Vendor\Zephir\Optimizers\OptimizerAbstract;
/** @internal */
class TensorGetNumThreadsOptimizer extends OptimizerAbstract
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
        $context->headersManager->add('include/settings', HeadersManager::POSITION_LAST);
        $resolvedParams = $call->getResolvedParams([], $context, $expression);
        $symbol = $context->backend->getVariableCode($symbolVariable);
        $context->codePrinter->output("tensor_get_num_threads({$symbol});");
        return new CompiledExpression('variable', $symbolVariable->getRealName(), $expression);
    }
}

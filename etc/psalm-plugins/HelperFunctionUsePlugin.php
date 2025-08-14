<?php

use PhpParser\Node\Expr;
use Psalm\CodeLocation;
use Psalm\Internal\Analyzer\StatementsAnalyzer;
use Psalm\Issue\PluginIssue;
use Psalm\IssueBuffer;
use Psalm\Plugin\EventHandler\Event\AfterFunctionCallAnalysisEvent;

class HelperFunctionUsePlugin implements \Psalm\Plugin\EventHandler\AfterFunctionCallAnalysisInterface
{
	public static function afterFunctionCallAnalysis(AfterFunctionCallAnalysisEvent $event): void
	{
		$expr = $event->getExpr();
		if ($expr->name instanceof Expr) {
			return;
		}

		// find where the called function was defined
		$functions = $event->getCodebase()->functions;
		$statementsSource = $event->getStatementsSource();
		$storage = $functions->getStorage(
			$statementsSource instanceof StatementsAnalyzer ?: null,
			$event->getFunctionId()
		);

		// if the function is a Kirby helper,
		// consider this function call an issue
		if ($storage->location->file_path === dirname(__FILE__, 3) . '/config/helpers.php') {
			IssueBuffer::accepts(
				new HelperFunctionUse(
					'Use of user-overridable Kirby helper "' . $storage->cased_name . '"',
					new CodeLocation($statementsSource, $expr->name)
				),
				$statementsSource->getSuppressedIssues()
			);
		}
	}
}

class HelperFunctionUse extends PluginIssue
{
}

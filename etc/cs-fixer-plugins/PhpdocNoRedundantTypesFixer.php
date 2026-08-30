<?php

use PhpCsFixer\AbstractFixer;
use PhpCsFixer\DocBlock\DocBlock;
use PhpCsFixer\Fixer\Phpdoc\NoSuperfluousPhpdocTagsFixer;
use PhpCsFixer\FixerDefinition\CodeSample;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\FixerDefinition\FixerDefinitionInterface;
use PhpCsFixer\Preg;
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;

/**
 * Removes the type from `@param` tags that only repeat the native
 * parameter type, while keeping the description.
 */
final class PhpdocNoRedundantTypesFixer extends AbstractFixer
{
	/**
	 * Splits `@param <type> $name <description>` into its parts
	 */
	private const PARAM = '/^(?<pre>\h*\*\h*@param)\h+(?<type>\S+)(?<name>\h+(?:&\h*)?(?:\.{3})?\$\w+)(?<description>.*)$/s';

	private NoSuperfluousPhpdocTagsFixer $fixer;

	public function __construct()
	{
		$this->fixer = new NoSuperfluousPhpdocTagsFixer();
		$this->fixer->configure(['allow_unused_params' => true]);
		parent::__construct();
	}

	public function getDefinition(): FixerDefinitionInterface
	{
		return new FixerDefinition(
			'Docblock `@param` tags must not repeat the native parameter type.',
			[
				new CodeSample(
					"<?php\n/**\n * @param string \$unit The unit\n */\nfunction f(string \$unit) {}\n"
				)
			]
		);
	}

	public function getName(): string
	{
		return 'Kirby/phpdoc_no_redundant_types';
	}

	/**
	 * Runs late, so that class names and tag order are already normalized
	 */
	public function getPriority(): int
	{
		return -30;
	}

	public function isCandidate(Tokens $tokens): bool
	{
		return
			$tokens->isTokenKindFound(T_DOC_COMMENT) === true &&
			$tokens->isTokenKindFound(T_FUNCTION) === true;
	}

	protected function applyFix(SplFileInfo $file, Tokens $tokens): void
	{
		$redundant = $this->redundant($file, $tokens);

		if ($redundant === []) {
			return;
		}

		$nth = -1;

		foreach ($tokens as $index => $token) {
			if ($token->isGivenKind(T_DOC_COMMENT) === false) {
				continue;
			}

			if (isset($redundant[++$nth]) === false) {
				continue;
			}

			$doc     = new DocBlock($token->getContent());
			$changed = false;

			foreach ($doc->getAnnotationsOfType('param') as $annotation) {
				$line = $doc->getLine($annotation->getStart());

				if ($line === null) {
					continue;
				}

				$match = [];

				if (Preg::match(self::PARAM, $line->getContent(), $match) === false) {
					continue;
				}

				// the built-in fixer removes tags without a description
				if (trim($match['description']) === '') {
					continue;
				}

				if (in_array($match['name'], $redundant[$nth], true) === false) {
					continue;
				}

				$line->setContent(
					$match['pre'] . $match['name'] . $match['description']
				);

				$changed = true;
			}

			if ($changed === true) {
				$tokens[$index] = new Token([T_DOC_COMMENT, $doc->getContent()]);
			}
		}
	}

	/**
	 * Returns the `@param` names of a docblock
	 */
	private function names(string $content): array
	{
		$doc   = new DocBlock($content);
		$names = [];

		foreach ($doc->getAnnotationsOfType('param') as $annotation) {
			$line  = $doc->getLine($annotation->getStart());
			$match = [];

			if (
				$line !== null &&
				Preg::match(self::PARAM, $line->getContent(), $match) === true
			) {
				$names[] = $match['name'];
			}
		}

		return $names;
	}

	/**
	 * Builds the code handed to the built-in fixer: the same file with
	 * every `@param` description removed, so only the type is left to judge
	 *
	 * @param $names Filled with the `@param` names per docblock
	 */
	private function probe(Tokens $tokens, array &$names): string
	{
		$probe = clone $tokens;
		$nth   = -1;

		foreach ($tokens as $index => $token) {
			if ($token->isGivenKind(T_DOC_COMMENT) === false) {
				continue;
			}

			$doc           = new DocBlock($token->getContent());
			$names[++$nth] = [];

			foreach ($doc->getAnnotationsOfType('param') as $annotation) {
				$start = $annotation->getStart();
				$line  = $doc->getLine($start);

				if ($line === null) {
					continue;
				}

				$match = [];

				if (Preg::match(self::PARAM, $line->getContent(), $match) === false) {
					continue;
				}

				$names[$nth][] = $match['name'];

				// keep the tag and its type, drop the description
				$line->setContent(
					$match['pre'] . ' ' . $match['type'] . $match['name'] . "\n"
				);

				// drop the remaining lines of a multi-line description
				for ($i = $start + 1; $i <= $annotation->getEnd(); $i++) {
					$doc->getLine($i)?->remove();
				}
			}

			$probe[$index] = new Token([T_DOC_COMMENT, $doc->getContent()]);
		}

		return $probe->generateCode();
	}

	/**
	 * Returns the `@param` names whose type says nothing the signature
	 * doesn't, indexed by the position of their docblock in the file
	 */
	private function redundant(SplFileInfo $file, Tokens $tokens): array
	{
		$before = [];
		$probe  = Tokens::fromCode($this->probe($tokens, $before));

		$this->fixer->fix($file, $probe);

		$after = [];
		$nth   = -1;

		foreach ($probe as $token) {
			if ($token->isGivenKind(T_DOC_COMMENT) === true) {
				$after[++$nth] = $this->names($token->getContent());
			}
		}

		$redundant = [];

		foreach ($before as $nth => $names) {
			// in the probe before, gone after: the type was redundant
			$gone = array_values(array_diff($names, $after[$nth] ?? []));

			if ($gone !== []) {
				$redundant[$nth] = $gone;
			}
		}

		return $redundant;
	}
}

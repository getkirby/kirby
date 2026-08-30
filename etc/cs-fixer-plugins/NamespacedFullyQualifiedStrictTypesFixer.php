<?php

use PhpCsFixer\AbstractFixer;
use PhpCsFixer\Fixer\ConfigurableFixerInterface;
use PhpCsFixer\Fixer\Import\FullyQualifiedStrictTypesFixer;
use PhpCsFixer\Fixer\WhitespacesAwareFixerInterface;
use PhpCsFixer\FixerConfiguration\FixerConfigurationResolverInterface;
use PhpCsFixer\FixerDefinition\FixerDefinitionInterface;
use PhpCsFixer\Tokenizer\Tokens;
use PhpCsFixer\WhitespacesFixerConfig;

/**
 * Shortens fully qualified class names in type declarations and docblocks,
 * but only in files that declare a namespace.
 */
final class NamespacedFullyQualifiedStrictTypesFixer extends AbstractFixer implements ConfigurableFixerInterface, WhitespacesAwareFixerInterface
{
	private FullyQualifiedStrictTypesFixer $fixer;

	public function __construct()
	{
		$this->fixer = new FullyQualifiedStrictTypesFixer();

		parent::__construct();
	}

	public function configure(array $configuration): void
	{
		$this->fixer->configure($configuration);
	}

	public function getConfigurationDefinition(): FixerConfigurationResolverInterface
	{
		return $this->fixer->getConfigurationDefinition();
	}

	public function getDefinition(): FixerDefinitionInterface
	{
		return $this->fixer->getDefinition();
	}

	public function getName(): string
	{
		return 'Kirby/fully_qualified_strict_types';
	}

	public function getPriority(): int
	{
		return $this->fixer->getPriority();
	}

	public function isCandidate(Tokens $tokens): bool
	{
		return
			$tokens->isTokenKindFound(T_NAMESPACE) === true &&
			$this->fixer->isCandidate($tokens) === true;
	}

	public function isRisky(): bool
	{
		return $this->fixer->isRisky();
	}

	public function setWhitespacesConfig(WhitespacesFixerConfig $config): void
	{
		$this->fixer->setWhitespacesConfig($config);
	}

	public function supports(SplFileInfo $file): bool
	{
		return $this->fixer->supports($file);
	}

	protected function applyFix(SplFileInfo $file, Tokens $tokens): void
	{
		$this->fixer->fix($file, $tokens);
	}
}

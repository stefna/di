<?php declare(strict_types=1);

namespace Moya\DependencyInjection\Definition;

final class DefinitionFile extends DefinitionArray
{
	private bool $initialized = false;

	public function __construct(
		private readonly string $file,
	) {
		parent::__construct([]);
	}

	public function getDefinition(string $name): ?callable
	{
		$this->initialize();
		return parent::getDefinition($name);
	}

	public function getDefinitions(): array
	{
		$this->initialize();
		return parent::getDefinitions();
	}

	private function initialize(): void
	{
		if ($this->initialized) {
			return;
		}

		if (!file_exists($this->file)) {
			throw new \RuntimeException("File '{$this->file}' not found");
		}

		$definitions = require $this->file;

		if (!is_array($definitions)) {
			throw new \RuntimeException("File '{$this->file}' should return an array of definitions");
		}

		$this->addDefinitions($definitions);

		$this->initialized = true;
	}
}

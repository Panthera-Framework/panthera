<?php
namespace Panthera\deployment;

/**
 * Executes database migrations all in proper order
 *
 * @author Damian Kęska <damian@pantheraframework.org>
 * @package Panthera\deployment\framework
 */
class updateSignalsIndexTask extends task
{
	/**
	 * This method will be executed after task will be verified by deployment management
	 *
	 * @throws \Panthera\FileNotFoundException
	 * @throws \Panthera\PantheraFrameworkException
	 *
	 * @author Damian Kęska <damian@pantheraframework.org>
	 * @return bool
	 */
	public function execute()
	{
		$collected = [];

		foreach ($this->deployApp->indexService->mixedFilesStructure as $dir)
		{
			foreach ($dir as $file => $state)
			{
				if (pathinfo($file, PATHINFO_EXTENSION) !== 'php')
				{
					continue;
				}

				$absolutePath = $this->app->getPath($file);

				$this->output('-> Parsing ' .$absolutePath);
				$signals = \signalIndexing::loadFile($absolutePath);

				if ($signals)
				{
					foreach ($signals as $slotName => &$slot)
					{
						foreach ($slot as &$signal)
						{
							unset($signal['phpDoc']);
						}

						$this->output('--> Found ' .$slotName. ' (' .count($slot). ')');
					}

					$collected = array_merge_recursive($collected, $signals);
				}
			}
		}

		// write collected signals to applicationIndex
		$this->deployApp->indexService->writeIndexFile('signals', $collected);

		return true;
	}
}
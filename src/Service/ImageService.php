<?php

namespace Logigator\Service;

class ImageService extends BaseService
{
	const GRID_SIZE = 16;
	const DEFAULT_COMP_WIDTH = 2;
	const COMP_WIDTH = [
		0 => 0,
		7 => 0,
		100 => 1,
		101 => 1,
		102 => 1,
		200 => 1,
		201 => 1
	];

	public function generateProjectImage(string $projectPath, int $width = 256, int $height = 256) {
		if (!file_exists($projectPath))
			return false;

		// retrieve project file
		$project = json_decode(file_get_contents($projectPath));

		$offsetX = PHP_INT_MAX;
		$offsetY = PHP_INT_MAX;
		$w = 0;
		$h = 0;

		foreach ($project->elements as $element) {
			// calculate endPos if missing
			$this->calculateEndPos($element);

			// calculate borders
			if (($element->typeId === 0 ? $element->pos->x + 0.5 : $element->pos->x) < $offsetX)
				$offsetX = $element->typeId === 0 ? $element->pos->x + 0.5 : $element->pos->x;
			if (($element->typeId === 0 ? $element->pos->y + 0.5 : $element->pos->y) < $offsetY)
				$offsetY = $element->typeId === 0 ? $element->pos->y + 0.5 : $element->pos->y;
			if (($element->typeId === 0 ? $element->endPos->x + 0.5 : $element->endPos->x) > $w)
				$w = $element->typeId === 0 ? $element->endPos->x + 0.5 : $element->endPos->x;
			if (($element->typeId === 0 ? $element->endPos->y + 0.5 : $element->endPos->y) > $h)
				$h = $element->typeId === 0 ? $element->endPos->y + 0.5 : $element->endPos->y;
		}

		// calculate variables so it gets scaled correctly
		$w = ($w - $offsetX) * self::GRID_SIZE + 1;
		$h = ($h - $offsetY) * self::GRID_SIZE + 1;
		$scaleFactor = ($height / $h > $width / $w) ? $width / $w : $height / $h;
		$offsetX -= ($width - $w * $scaleFactor) / self::GRID_SIZE / $scaleFactor / 2;
		$offsetY -= ($height - $h * $scaleFactor) / self::GRID_SIZE / $scaleFactor / 2;

		// color definitions
		$image = imagecreatetruecolor($width, $height);
		$background = imagecolorallocatealpha($image, 0, 0, 0, 127);
		$lineColor = imagecolorallocatealpha($image, 39, 174, 96, $scaleFactor > 1 / self::GRID_SIZE ? 0 : 64);

		imagealphablending($image, true);
		imagesavealpha($image, true);
		imagefill($image, 0, 0, $background);

		// main rendering loop
		foreach ($project->elements as $element) {
			$element->pos->x -= $offsetX;
			$element->pos->y -= $offsetY;
			$element->endPos->x -= $offsetX;
			$element->endPos->y -= $offsetY;

			$this->drawElement($element, $image, $lineColor, $scaleFactor);
		}

		return $image;
	}

	private function calculateEndPos($element) {
		if (!isset($element->endPos)) {
			if ($element->rotation === 0 || $element->rotation === 2) {
				$element->endPos = (object) [
					'x' => $element->pos->x + (isset(self::COMP_WIDTH[$element->typeId]) ? self::COMP_WIDTH[$element->typeId] : self::DEFAULT_COMP_WIDTH),
					'y' => $element->pos->y + (($element->numInputs > $element->numOutputs) ? $element->numInputs : $element->numOutputs)
				];
			} else {
				$element->endPos = (object) [
					'x' => $element->pos->x + (($element->numInputs > $element->numOutputs) ? $element->numInputs : $element->numOutputs),
					'y' => $element->pos->y + (isset(self::COMP_WIDTH[$element->typeId]) ? self::COMP_WIDTH[$element->typeId] : self::DEFAULT_COMP_WIDTH)
				];
			}
		}
	}

	private function drawElement($element, $image, $lineColor, $scale) {
		switch ($element->typeId) {
			case 0:
				imageline(
					$image,
					($element->pos->x * self::GRID_SIZE + self::GRID_SIZE / 2) * $scale,
					($element->pos->y * self::GRID_SIZE + self::GRID_SIZE / 2) * $scale,
					($element->endPos->x * self::GRID_SIZE + self::GRID_SIZE / 2) * $scale,
					($element->endPos->y * self::GRID_SIZE + self::GRID_SIZE / 2) * $scale,
					$lineColor
				);
				break;
			default:
				imageline(
					$image,
					$element->pos->x * self::GRID_SIZE * $scale,
					$element->pos->y * self::GRID_SIZE * $scale,
					$element->endPos->x * self::GRID_SIZE * $scale,
					$element->pos->y * self::GRID_SIZE * $scale,
					$lineColor
				);
				imageline(
					$image,
					$element->pos->x * self::GRID_SIZE * $scale,
					$element->pos->y * self::GRID_SIZE * $scale,
					$element->pos->x * self::GRID_SIZE * $scale,
					$element->endPos->y * self::GRID_SIZE * $scale,
					$lineColor
				);
				imageline(
					$image,
					$element->endPos->x * self::GRID_SIZE * $scale,
					$element->endPos->y * self::GRID_SIZE * $scale,
					$element->pos->x * self::GRID_SIZE * $scale,
					$element->endPos->y * self::GRID_SIZE * $scale,
					$lineColor
				);
				imageline(
					$image,
					$element->endPos->x * self::GRID_SIZE * $scale,
					$element->endPos->y * self::GRID_SIZE * $scale,
					$element->endPos->x * self::GRID_SIZE * $scale,
					$element->pos->y * self::GRID_SIZE * $scale,
					$lineColor
				);
				break;
		}
	}
}

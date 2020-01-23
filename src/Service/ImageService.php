<?php

namespace Logigator\Service;

class ImageService extends BaseService
{
	const GRID_SIZE = 16;
	const DEFAULT_COMP_WIDTH = 3;
	const COMP_WIDTH = [
		0 => 0,
		1 => 2,
		2 => 2,
		3 => 2,
		4 => 2,
		5 => 2,
		7 => 0,
		100 => 1,
		101 => 1,
		102 => 1,
		200 => 1,
		201 => 1,
		202 => 1
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
			if ($element->pos->x < $offsetX)
				$offsetX = $element->pos->x;
			if ($element->pos->y < $offsetY)
				$offsetY = $element->pos->y;
			if ($element->endPos->x + 0.5 > $w)
				$w = $element->endPos->x + 0.5;
			if ($element->endPos->y + 0.5 > $h)
				$h = $element->endPos->y + 0.5;
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
		$font = imagecolorallocate($image, 255, 255, 255);

		imagealphablending($image, true);
		imagesavealpha($image, true);
		imagefill($image, 0, 0, $background);

		// main rendering loop
		foreach ($project->elements as $element) {
			$element->pos->x -= $offsetX;
			$element->pos->y -= $offsetY;
			$element->endPos->x -= $offsetX;
			$element->endPos->y -= $offsetY;

			$this->drawElement($element, $image, $lineColor, $font, $scaleFactor);
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

	private function drawElement($element, $image, $lineColor, $font, $scale) {
		if ($scale > 0.2) {
			$coords = [
				0 => [
					'x' => $element->pos->x * self::GRID_SIZE,
					'y' => $element->pos->y * self::GRID_SIZE + self::GRID_SIZE / 2,
					'x2' => $element->pos->x * self::GRID_SIZE - self::GRID_SIZE / 2,
					'y2' => $element->pos->y * self::GRID_SIZE + self::GRID_SIZE / 2
				],
				1 => [
					'x' => $element->pos->x * self::GRID_SIZE + self::GRID_SIZE / 2,
					'y' => $element->pos->y * self::GRID_SIZE,
					'x2' => $element->pos->x * self::GRID_SIZE + self::GRID_SIZE / 2,
					'y2' => $element->pos->y * self::GRID_SIZE - self::GRID_SIZE / 2
				],
				2 => [
					'x' => $element->endPos->x * self::GRID_SIZE,
					'y' => $element->pos->y * self::GRID_SIZE + self::GRID_SIZE / 2,
					'x2' => $element->endPos->x * self::GRID_SIZE + self::GRID_SIZE / 2,
					'y2' => $element->pos->y * self::GRID_SIZE + self::GRID_SIZE / 2
				],
				3 => [
					'x' => $element->pos->x * self::GRID_SIZE + self::GRID_SIZE / 2,
					'y' => $element->endPos->y * self::GRID_SIZE,
					'x2' => $element->pos->x * self::GRID_SIZE + self::GRID_SIZE / 2,
					'y2' => $element->endPos->y * self::GRID_SIZE + self::GRID_SIZE / 2
				]
			];

			switch ($element->typeId) {
				case 0:
				case 7:
					break;
				default:
					for ($i = 0; $i < $element->numInputs; $i++) {
						imageline(
							$image,
							($coords[$element->rotation]['x'] + (($element->rotation % 2 === 1) ? self::GRID_SIZE * $i : 0)) * $scale,
							($coords[$element->rotation]['y'] + (($element->rotation % 2 === 0) ? self::GRID_SIZE * $i : 0)) * $scale,
							($coords[$element->rotation]['x2'] + (($element->rotation % 2 === 1) ? self::GRID_SIZE * $i : 0)) * $scale,
							($coords[$element->rotation]['y2'] + (($element->rotation % 2 === 0) ? self::GRID_SIZE * $i : 0)) * $scale,
							$lineColor
						);
					}
					for ($i = 0; $i < $element->numOutputs; $i++) {
						imageline(
							$image,
							($coords[($element->rotation + 2) % 4]['x'] + (($element->rotation % 2 === 1) ? self::GRID_SIZE * $i : 0)) * $scale,
							($coords[($element->rotation + 2) % 4]['y'] + (($element->rotation % 2 === 0) ? self::GRID_SIZE * $i : 0)) * $scale,
							($coords[($element->rotation + 2) % 4]['x2'] + (($element->rotation % 2 === 1) ? self::GRID_SIZE * $i : 0)) * $scale,
							($coords[($element->rotation + 2) % 4]['y2'] + (($element->rotation % 2 === 0) ? self::GRID_SIZE * $i : 0)) * $scale,
							$lineColor
						);
					}
					break;
			}
		}

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
			case 7:
				imagefilledrectangle(
					$image,
					($element->pos->x * self::GRID_SIZE + self::GRID_SIZE / 2 - 2.5) * $scale,
					($element->pos->y * self::GRID_SIZE + self::GRID_SIZE / 2 - 2.5) * $scale,
					($element->pos->x * self::GRID_SIZE + self::GRID_SIZE / 2 + 2.5) * $scale,
					($element->pos->y * self::GRID_SIZE + self::GRID_SIZE / 2 + 2.5) * $scale,
					$lineColor
				);
				imagettftext(
					$image,
					$scale * (self::GRID_SIZE - 3),
					0,
					($element->pos->x * self::GRID_SIZE + self::GRID_SIZE) * $scale,
					($element->pos->y * self::GRID_SIZE + self::GRID_SIZE - 1.5) * $scale,
					$font,
					$_SERVER['DOCUMENT_ROOT'] . '/data/Roboto-Regular.ttf',
					$element->data
				);
				break;
			case 200:
				imagerectangle(
					$image,
					$element->pos->x * self::GRID_SIZE * $scale,
					$element->pos->y * self::GRID_SIZE * $scale,
					$element->endPos->x * self::GRID_SIZE * $scale,
					$element->endPos->y * self::GRID_SIZE * $scale,
					$lineColor
				);
				imagerectangle(
					$image,
					($element->pos->x * self::GRID_SIZE + 3) * $scale,
					($element->pos->y * self::GRID_SIZE + 3) * $scale,
					($element->endPos->x * self::GRID_SIZE - 3) * $scale,
					($element->endPos->y * self::GRID_SIZE - 3) * $scale,
					$lineColor
				);
				break;
			case 201:
				imagerectangle(
					$image,
					$element->pos->x * self::GRID_SIZE * $scale,
					$element->pos->y * self::GRID_SIZE * $scale,
					$element->endPos->x * self::GRID_SIZE * $scale,
					$element->endPos->y * self::GRID_SIZE * $scale,
					$lineColor
				);
				imageline(
					$image,
					($element->pos->x * self::GRID_SIZE) * $scale,
					($element->endPos->y * self::GRID_SIZE - 4) * $scale,
					($element->endPos->x * self::GRID_SIZE) * $scale,
					($element->endPos->y * self::GRID_SIZE - 4) * $scale,
					$lineColor
				);
				break;
			case 202:
				imagefilledellipse(
					$image,
					($element->pos->x * self::GRID_SIZE + self::GRID_SIZE / 2) * $scale,
					($element->pos->y * self::GRID_SIZE + self::GRID_SIZE / 2) * $scale,
					self::GRID_SIZE * $scale,
					self::GRID_SIZE * $scale,
					$lineColor
				);
				break;
			default:
				imagerectangle(
					$image,
					$element->pos->x * self::GRID_SIZE * $scale,
					$element->pos->y * self::GRID_SIZE * $scale,
					$element->endPos->x * self::GRID_SIZE * $scale,
					$element->endPos->y * self::GRID_SIZE * $scale,
					$lineColor
				);
				break;
		}
	}
}

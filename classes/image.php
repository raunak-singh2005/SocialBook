<?php 

/**
 * Image manipulation class for generating filenames, cropping, resizing, and creating thumbnails.
 */
class Image
{
	/**
	 * Generate a random filename of given length.
	 */
	public function generateFilename($length)
	{
		$characters = array_merge(range(0, 9), range('a', 'z'), range('A', 'Z'));
		$text = "";

		for ($x = 0; $x < $length; $x++) {
			$random = rand(0, count($characters) - 1);
			$text .= $characters[$random];
		}

		return $text;
	}

	/**
	 * Crop an image to the specified width and height, centering the crop.
	 */
	public function cropImage($originalFileName, $croppedFileName, $maxWidth, $maxHeight)
	{
		if (!file_exists($originalFileName)) {
			return;
		}

		$extension = strtolower(pathinfo($originalFileName, PATHINFO_EXTENSION));
		$originalImage = $this->createImageFromFile($originalFileName, $extension);

		$originalWidth = imagesx($originalImage);
		$originalHeight = imagesy($originalImage);

		// Calculate new dimensions while preserving aspect ratio
		if ($originalHeight > $originalWidth) {
			$ratio = $maxWidth / $originalWidth;
			$newWidth = $maxWidth;
			$newHeight = $originalHeight * $ratio;
		} else {
			$ratio = $maxHeight / $originalHeight;
			$newHeight = $maxHeight;
			$newWidth = $originalWidth * $ratio;
		}

		// Adjust if max width and height are different
		if ($maxWidth != $maxHeight) {
			if ($maxHeight > $maxWidth) {
				$adjustment = ($maxHeight > $newHeight) ? ($maxHeight / $newHeight) : ($newHeight / $maxHeight);
			} else {
				$adjustment = ($maxWidth > $newWidth) ? ($maxWidth / $newWidth) : ($newWidth / $maxWidth);
			}
			$newWidth *= $adjustment;
			$newHeight *= $adjustment;
		}

		// Resize image
		$newImage = imagecreatetruecolor($newWidth, $newHeight);
		$this->preserveTransparency($newImage, $extension);
		imagecopyresampled($newImage, $originalImage, 0, 0, 0, 0, $newWidth, $newHeight, $originalWidth, $originalHeight);
		imagedestroy($originalImage);

		// Calculate crop position
		list($x, $y) = $this->calculateCropPosition($newWidth, $newHeight, $maxWidth, $maxHeight);

		// Crop image
		$newCroppedImage = imagecreatetruecolor($maxWidth, $maxHeight);
		$this->preserveTransparency($newCroppedImage, $extension);
		imagecopyresampled($newCroppedImage, $newImage, 0, 0, $x, $y, $maxWidth, $maxHeight, $maxWidth, $maxHeight);
		imagedestroy($newImage);

		// Save cropped image
		$this->saveImageToFile($newCroppedImage, $croppedFileName, $extension);
		imagedestroy($newCroppedImage);
	}

	/**
	 * Resize an image to fit within max width and height, preserving aspect ratio.
	 */
	public function resizeImage($originalFileName, $resizedFileName, $maxWidth, $maxHeight, $extension = null)
	{
		if (!file_exists($originalFileName)) {
			return;
		}

		if (!$extension) {
			$extension = strtolower(pathinfo($originalFileName, PATHINFO_EXTENSION));
		}
		$originalImage = $this->createImageFromFile($originalFileName, $extension);

		$originalWidth = imagesx($originalImage);
		$originalHeight = imagesy($originalImage);

		// Calculate new dimensions
		if ($originalHeight > $originalWidth) {
			$ratio = $maxWidth / $originalWidth;
			$newWidth = $maxWidth;
			$newHeight = $originalHeight * $ratio;
		} else {
			$ratio = $maxHeight / $originalHeight;
			$newHeight = $maxHeight;
			$newWidth = $originalWidth * $ratio;
		}

		// Resize image
		$newImage = imagecreatetruecolor($newWidth, $newHeight);
		$this->preserveTransparency($newImage, $extension);
		imagecopyresampled($newImage, $originalImage, 0, 0, 0, 0, $newWidth, $newHeight, $originalWidth, $originalHeight);
		imagedestroy($originalImage);

		// Save resized image
		$this->saveImageToFile($newImage, $resizedFileName, $extension);
		imagedestroy($newImage);
	}

	/**
	 * Create a cover thumbnail (1366x488) for the given image.
	 */
	public function getThumbCover($filename)
	{
		$thumbnail = $filename . "_cover_thumb.jpg";
		if (file_exists($thumbnail)) {
			return $thumbnail;
		}

		$this->cropImage($filename, $thumbnail, 1366, 488);

		return file_exists($thumbnail) ? $thumbnail : $filename;
	}

	/**
	 * Create a profile thumbnail (600x600) for the given image.
	 */
	public function getThumbProfile($filename)
	{
		$thumbnail = $filename . "_profile_thumb.jpg";
		if (file_exists($thumbnail)) {
			return $thumbnail;
		}

		$this->cropImage($filename, $thumbnail, 600, 600);

		return file_exists($thumbnail) ? $thumbnail : $filename;
	}

	/**
	 * Create a post thumbnail (600x600) for the given image.
	 */
	public function getThumbPost($filename)
	{
		$thumbnail = $filename . "_post_thumb.jpg";
		if (file_exists($thumbnail)) {
			return $thumbnail;
		}

		$this->cropImage($filename, $thumbnail, 600, 600);

		return file_exists($thumbnail) ? $thumbnail : $filename;
	}

	// --- Private helper methods ---

	/**
	 * Create an image resource from file based on extension.
	 */
	private function createImageFromFile($filename, $extension)
	{
		switch ($extension) {
			case 'png':
				return imagecreatefrompng($filename);
			case 'gif':
				return imagecreatefromgif($filename);
			case 'jpg':
			case 'jpeg':
			default:
				return imagecreatefromjpeg($filename);
		}
	}

	/**
	 * Save an image resource to file based on extension.
	 */
	private function saveImageToFile($image, $filename, $extension)
	{
		switch ($extension) {
			case 'png':
				imagepng($image, $filename);
				break;
			case 'gif':
				imagegif($image, $filename);
				break;
			case 'jpg':
			case 'jpeg':
			default:
				imagejpeg($image, $filename, 90);
				break;
		}
	}

	/**
	 * Preserve transparency for PNG and GIF images.
	 */
	private function preserveTransparency($image, $extension)
	{
		if ($extension == 'png' || $extension == 'gif') {
			imagecolortransparent($image, imagecolorallocatealpha($image, 0, 0, 0, 127));
			imagealphablending($image, false);
			imagesavealpha($image, true);
		}
	}

	/**
	 * Calculate crop position for centering the crop.
	 */
	private function calculateCropPosition($newWidth, $newHeight, $maxWidth, $maxHeight)
	{
		if ($maxWidth != $maxHeight) {
			if ($maxWidth > $maxHeight) {
				$diff = abs($newHeight - $maxHeight);
				return [0, round($diff / 2)];
			} else {
				$diff = abs($newWidth - $maxWidth);
				return [round($diff / 2), 0];
			}
		} else {
			if ($newHeight > $newWidth) {
				$diff = $newHeight - $newWidth;
				return [0, round($diff / 2)];
			} else {
				$diff = $newWidth - $newHeight;
				return [round($diff / 2), 0];
			}
		}
	}
}

<?php


namespace Logigator\Api\User;


use Logigator\Api\ApiHelper;
use Logigator\Api\BaseController;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Ramsey\Uuid\Uuid;
use Slim\Exception\HttpBadRequestException;
use Slim\Exception\HttpInternalServerErrorException;

class UploadPicture extends BaseController
{
	public function __invoke(ServerRequestInterface $request, ResponseInterface $response, array $args)
	{
		$ALLOWED_FILETYPES = [ IMAGETYPE_JPEG, IMAGETYPE_PNG ];

		$picture = $request->getUploadedFiles();
		if(!isset($picture['picture']) || is_array($picture['picture']))
			throw new HttpBadRequestException($request, 'No picture received.');
		$picture = $picture['picture'];

		if($picture->getError() === UPLOAD_ERR_INI_SIZE)
			throw new HttpBadRequestException($request, 'Max file size exceeded.');

		if($picture->getError() !== UPLOAD_ERR_OK)
			throw new \Exception('getError() returned error code: ' . $picture->getError());

		if($picture->getSize() > 1024 * 1024 * 2)
			throw new HttpBadRequestException($request, 'Max file size exceeded.');

		if(!in_array(exif_imagetype($_FILES['picture']['tmp_name']), $ALLOWED_FILETYPES))
			throw new HttpBadRequestException($request, 'File type not supported.');

		$fileName = $this->container->get('DbalService')->getQueryBuilder()
			->select('profile_image')
			->from('users')
			->where('pk_id = ?')
			->setParameter(0, (int)$this->getTokenPayload()->sub)
			->execute()
			->fetch()['profile_image'];

		if(!$fileName) {
			$fileName = Uuid::uuid4()->toString();

			$this->container->get('DbalService')->getQueryBuilder()
				->update('users')
				->set('profile_image', ':image')
				->where('pk_id = :id')
				->setParameter('id', (int)$this->getTokenPayload()->sub)
				->setParameter('image', $fileName)
				->execute();
		}

		$picture->moveTo(ApiHelper::getProfileImagePath($this->container, $fileName));

		return ApiHelper::createJsonResponse($response, ['file' => $fileName]);
	}
}

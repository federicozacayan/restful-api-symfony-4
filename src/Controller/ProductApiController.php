<?php
namespace App\Controller;

use FOS\RestBundle\Controller\AbstractFOSRestController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\Product;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

class ProductApiController extends AbstractFOSRestController
{
    
    public function __construct(
        ProductRepository $productRepository,
        EntityManagerInterface $entityManagerInterface
        ){
            $this->productRepository = $productRepository;
            $this->entityManager = $entityManagerInterface;
    }
    
    //Get all
    public function productsAction(){
        $data = $this->productRepository->findAll();
        return $this->view($data, Response::HTTP_OK);
    }
    
    //Get by ID
    public function getProductsAction(Product $product){
        return $this->view($product, Response::HTTP_OK);
    }
    
    //Create
    /**
     * @ParamConverter("product", converter="fos_rest.request_body")
     */
    public function postProductsAction(
        Product $product,
        ConstraintViolationListInterface $validationErrors
        ){
            
            if (count($validationErrors) > 0) {
                return $this->view($validationErrors , Response::HTTP_BAD_REQUEST);
            }
            $this->entityManager->persist($product);
            $this->entityManager->flush();
            return $this->view($product , Response::HTTP_CREATED);
    }
    
    //Update some fields
    public function patchProductsAction(
        Product $product,
        Request $request,
        ValidatorInterface $validator
        ){
            return $this->update($product, $request, $validator);
    }
    
    //Update all fields
    public function putProductsAction(
        Product $product,
        Request $request,
        ValidatorInterface $validator
        ){
            return $this->update($product, $request, $validator);
    }
    
    //update
    private function update($product, $request, $validator){
        $json = $request->getContent();
        $newData = json_decode($json, true);
        
        foreach($newData as $propertyName => $value){
            $method = 'set'.ucfirst($propertyName);
            if(method_exists($product,$method)){
                $product->$method($value);
            }
        }
        
        $validationErrors = $validator->validate($product);
        if (count($validationErrors) > 0) {
            return $this->view($validationErrors , Response::HTTP_BAD_REQUEST);
        }
        
        $this->entityManager->persist($product);
        $this->entityManager->flush();
        return $this->view($product , Response::HTTP_OK);
    }
    
    //Delete
    public function deleteProductsAction(Product $product){
        $this->entityManager->remove($product);
        $this->entityManager->flush();
        return $this->view($product , Response::HTTP_OK);
    }
}
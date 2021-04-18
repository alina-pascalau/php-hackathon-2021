<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Building;
use App\Entity\Programme;
use App\Entity\Room;
use App\Entity\User;

class ApiController extends AbstractController 
{

    /**
     * Creates a new programme
     * 
     * @Route("/api/programme", name="programme", methods={"POST"})
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function createProgrammeAction(Request $request): JsonResponse 
    {
        try {
            //validate the request and the token
            $dataRequest = $this->validateRequest($request);
            //check for auth
            if ($this->checkAuth($request)) {
                //validate required parameters
                $name = $this->validateParameter('name', $dataRequest);
                $maxParticipants = $this->validateParameter('maxParticipants', $dataRequest);
                $startdate = $this->validateDateParameter('startdate', $dataRequest);
                $enddate = $this->validateDateParameter('enddate', $dataRequest);
                $roomId = $this->validateParameter('room', $dataRequest);

                //create the programme
                $programme = new Programme();
                $programme->setName($name);
                $programme->setMaxParticipants($maxParticipants);

                $programme->setStarttime($startdate);
                $programme->setEndtime($enddate);

                //check if room is available in that interval
                $room = $this->getDoctrine()->getRepository(Room::class)->findOneBy(['Name' => $roomName]);
                if (!$room) {
                    throw new \Exception('Room does not exist');
                }
                $isBooked = $this->getDoctrine()->getRepository(Programme::class)
                        ->checkIfAvailable($room, $startdate);

                if ($isBooked) {
                    throw new \Exception('Room is not available');
                }
                $programme->setRoom($room);

                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->persist($programme);
                $entityManager->flush();

                //response
                $data = [
                    'success' => "Programme added successfully",
                    'id' => $programme->getId()
                ];
                return $this->response($data, 200);
            }
        } catch (\Exception $e) {
            $data = [
                'errors' => $e->getMessage(),
            ];
            return $this->response($data, 400);
        }
    }
    
    /**
     * Delete programme
     * 
     * @Route("/api/programme/{id}", name="programme_delete", methods={"DELETE"})
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function deleteProgrammeAction(int $id, Request $request): JsonResponse 
    {
         try {
            //validate the request and the token
            $dataRequest = $this->checkAuth($request);
            //check for auth
            if ($this->checkAuth($request)) {
                //validate required parameters
//                $programmeId = $this->validateParameter('id', $dataRequest);
//                $userCNP = $this->validateParameter('CNP', $dataRequest);

                
                //get programme
                $programme = $this->getDoctrine()->getRepository(Programme::class)->find($id);
                if (!$programme) {
                    throw new \Exception('Programme not found');
                }
                
                //delete programme
                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->remove($programme);
                $entityManager->flush();
            }

            //response
            $data = [
                'success' => "Programme was deleted successfully",
            ];
            return $this->response($data, 200);
            
        } catch (\Exception $e) {
            $data = [
                'errors' => $e->getMessage(),
            ];
            return $this->response($data, 400);
        }
    }
    
     /**
     * Lists all programmes
     * 
     * @Route("/api/programmes", name="programmes", methods={"GET"})
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function getProgrammesAction(Request $request): JsonResponse 
    {
        try {
            $programmes = $this->getDoctrine()->getRepository(Programme::class)->findAll();
            
            foreach ($programmes as $programme)
            {
                $data[] = [
                    'id' => $programme->getId(),
                    'name' => $programme->getName(),
                    'startTime' => $programme->getStarttime(),
                    'endTime' => $programme->getEndtime(),
                    'maxParticipants' => $programme->getMaxParticipants(),
                    'registeredParticipants' => $programme->getUsers()->count()
                ];
            }
            //response
            return $this->response($data, 200);
            
        } catch (\Exception $e) {
            $data = [
                'errors' => $e->getMessage(),
            ];
            return $this->response($data, 400);
        }
    }
    
    /**
     * Register a new user
     * 
     * @Route("/api/user", name="user", methods={"POST"})
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function registerUserAction(Request $request): JsonResponse 
    {
        try {
            //validate the request and the token
            $dataRequest = $this->validateRequest($request);
            //check for auth (only admins can add users)
            if ($this->checkAuth($request)) {
                //validate required parameters
                $name = $this->validateParameter('name', $dataRequest);
                $CNP = $this->validateCNPParameter('CNP', $dataRequest);

                //create the user
                $user = new User();
                $user->setName($name);
                $user->setCNP($CNP);

                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->persist($user);
                $entityManager->flush();

                //response
                $data = [
                    'success' => "User added successfully",
                    'id' => $user->getId()
                ];
                return $this->response($data, 200);
            }
        } catch (\Exception $e) {
            $data = [
                'errors' => $e->getMessage(),
            ];
            return $this->response($data, 400);
        }
    }
    

    /**
     * Registers a user to a programme
     * 
     * @Route("/api/programme/register", name="register", methods={"POST"})
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function bookProgrammeAction(Request $request): JsonResponse 
    {
        try{
            $dataRequest = $this->validateRequest($request);
            
            //validate required parameters
            $programmeName = $this->validateParameter('programmeName', $dataRequest);
            $userCNP = $this->validateParameter('CNP', $dataRequest);
            
            //get user
            $user = $this->getDoctrine()->getRepository(User::class)->findOneBy(['CNP' => $userCNP]);
            if (!$user) {
                throw new \Exception('User not found');
            }
            
            //get programme
            $programme = $this->getDoctrine()->getRepository(Programme::class)->findOneBy(['Name' => $programmeName]);
            if (!$programme) {
                throw new \Exception('Programme not found');
            }
     
            //check if programme is fully booked
            if($this->isFullBooked($programme)){
                throw new \Exception('Programme is full booked');
            }
            
            //check is timeline is ok
            if(!$this->isTimeIntervalFree($programme, $user)){
                throw new \Exception('User is registered to another programme in the same time');
            }
            
            //register the user
            $programme->addUser($user);
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($programme);
            $entityManager->flush();
            
            
            //response
            $data = [
                'success' => "User registered successfully",
                'user' => $user->getId(),
                'programme' => $programme->getId()
            ];
            
            return $this->response($data, 200);
            
        } catch (\Exception $e) {
            $data = [
                'errors' => $e->getMessage(),
            ];
            return $this->response($data, 400);
        }
        
    }
    
    private function isTimeIntervalFree(Programme $programme, User $user)
    {
        $usersProgrammes = $user->getProgrammes();
        
        foreach ($usersProgrammes as $uProgramme)
        {
            if($uProgramme->getStarttime() == $programme->getStarttime()){
                return false;
            }
        }
        
        return true;
        
    }
    
    /**
     * Checks if programme's max nr of participants was achieved 
     * 
     * @param Programme $programme
     * @return bool
     */
    
    private function isFullBooked (Programme $programme): bool
    {
        $registeredUsers = $programme->getUsers()->count();
        if (($registeredUsers + 1) <= $programme->getMaxParticipants()) {
            return false;
        }
        
        return true;
    }

    /**
     * Creates the response
     * 
     * @param array $data
     * @param int $status
     * @param array $headers
     * @return JsonResponse
     */
    private function response(array $data, int $status = 200, array $headers = []): JsonResponse 
    {
        return new JsonResponse($data, $status, $headers);
    }

    /**
     * Validates date type
     * 
     * @param string $name
     * @param array $dataRequest
     * @return \DateTime
     * @throws \Exception
     */
    private function validateDateParameter(string $name, array $dataRequest): \DateTime 
    {
        if (!isset($dataRequest[$name])) {
            throw new \Exception($name . ' is required');
        }

        $date = \DateTime::createFromFormat('d-m-Y H:i', $dataRequest[$name]);
        if (!$date) {
            throw new \Exception('Date format should be: d-m-Y H:i');
        }

        return $date;
    }

    /**
     * Validates required parameter
     * 
     * @param string $name
     * @param array $dataRequest
     * @return string
     * @throws \Exception
     */
    private function validateParameter(string $name, array $dataRequest): string 
    {
        if (!isset($dataRequest[$name])) {
            throw new \Exception($name . ' is required');
        }

        return $dataRequest[$name];
    }
    
    /**
     * Validates the required CNP
     * 
     * @param string $name
     * @param array $dataRequest
     * @return string
     * @throws \Exception
     */
    private function validateCNPParameter(string $name, array $dataRequest): string
    {
        if (!isset($dataRequest[$name])) {
            throw new \Exception($name . ' is required');
        }
        if((!preg_match("/^[0-9]{13}$/", $dataRequest[$name]))){
            throw new \Exception('CNP not valid');
        }
        //CNP should be unique
        $user = $this->getDoctrine()->getRepository(User::class)->findOneBy(['CNP' => $dataRequest[$name]]);
        if($user){
            throw new \Exception('User already registered');
        }
        
        return $dataRequest[$name];
    }

    /**
     * Validates the request
     * 
     * @param Request $request
     * @return array|null
     * @throws \Exception
     */
    private function validateRequest(Request $request): ?array 
    {
        $data = json_decode($request->getContent(), true);

        if ($data === null) {
            throw new \Exception('Input failed validation');
        }

        return $data;
    }

    /**
     * Checks is request is authorized
     * 
     * @param Request $request
     * @return bool
     * @throws \Exception
     */
    private function checkAuth(Request $request): bool 
    {
        //TODO - check for header token
        if ($request->headers->get('token') == null) {
            throw new \Exception('Not authorized');
        }

        return true;
    }

}

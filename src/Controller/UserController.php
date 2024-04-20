<?php

namespace App\Controller;

use App\Entity\Entreprise;
use App\Entity\Job;
use App\Entity\User;
use App\Repository\EntrepriseRepository;
use App\Repository\JobRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route("/api/user", name: "app_user_")]
class UserController extends AbstractController
{
    private $userRep;
    private $entrepriseRep;
    private $jobRep;
    private $validator;
    private $serializer;
    private $em;

    public function __construct(UserRepository $userRep,
                                EntrepriseRepository $entrepriseRep,
                                JobRepository $jobRep,
                                SerializerInterface $serializer,
                                EntityManagerInterface $em,
                                ValidatorInterface $validator)
    {
        $this->userRep = $userRep;
        $this->entrepriseRep = $entrepriseRep;
        $this->jobRep = $jobRep;
        $this->serializer = $serializer;
        $this->em = $em;
        $this->validator = $validator;
    }

    /**
     * Create a new user
     * 
     * @param Request $request request's object
     * 
     * @method POST
     *
     * @return JsonResponse
     */
    #[Route("/register", name: "register", methods: ["POST"])]
    public function register(Request $request): JsonResponse
    {
        $user = $this->serializer->deserialize($request->getContent(), User::class,"json");

        $errors = $this->validator->validate($user);
        if ($errors->count() > 0) {

            return new JsonResponse($this->serializer->serialize($errors,"json"), Response::HTTP_BAD_REQUEST);
        }
        $now = new \DateTime("now");
        if ($now->diff($user->getBirthdate())->y >= 150) {

            return new JsonResponse(["message" => "people older than 150 years old can't suscribe to this app", Response::HTTP_BAD_REQUEST]);
        }

        $this->em->persist($user);
        $this->em->flush();

        return new JsonResponse(null, Response::HTTP_CREATED);
    }

    /**
     * Add job to user
     * @param Request $request request's object
     * @param User $user user
     * 
     * @method POST
     * 
     * @return JsonResponse
     */
    #[Route("/{id}/add/job", name: "add_job", methods: ["POST"])]
    public function addJob(Request $request, User $user): JsonResponse
    {
        $job = $this->serializer->deserialize($request->getContent(), Job::class,"json");

        if ($job->getEndingDate() !== null && $job->getEndingDate() < $job->getStartingDate()) {

            return new JsonResponse(["message" => "startingDate cannot be latest than endingDate", Response::HTTP_BAD_REQUEST]);
        }

        $entrepriseName = $request->toArray()["entreprise"];
        if (!$entrepriseName) {

            return new JsonResponse(["message" => "job has to be link to an entreprise", Response::HTTP_BAD_REQUEST]);
        }
        if (!$entreprise = $this->entrepriseRep->findOneBy(["name" => $entrepriseName])) {

            $entreprise = new Entreprise();
            $entreprise->setName($entrepriseName);
            $this->em->persist($entreprise);
        }

        $job->setEmployee($user);
        $job->setEntreprise($entreprise);

        $this->em->persist($job);
        $this->em->flush();

        return new JsonResponse(["message" => "user has a new experience", Response::HTTP_CREATED]);
    }

    /**
     * List users
     * 
     * @method GET
     * 
     * @return JsonResponse
     */
    #[Route("/", name: "list", methods: ["GET"])]
    public function listUsers(): JsonResponse
    {
        $users = $this->userRep->orderByAlpha();
        $res = [];
        $now = new \DateTime("now");
        foreach($users as $user) {

            $age = $now->diff($user->getBirthdate())->y;
            $job = $this->jobRep->findCurrentJobOf($user);
            $job ? $current = ["user" => $user, "age" => $age, "jobs" => $job] : $current = ["user" => $user, "age" => $age, "jobs" => "actually no job"];
            array_push($res, $current);
        }
        $json = $this->serializer->serialize($res, "json", ["groups" => "getCurentJob"]);

        return new JsonResponse($json, Response::HTTP_OK, ["accept" => "json"], true);
    }

    /**
     * List users of an entreprise
     * 
     * @param Entreprise $entreprise entreprise
     * 
     * @method GET
     * 
     * @return JsonResponse
     */
    #[Route("/entreprise/{name}", name: "list_employees_of", methods: ["GET"])]
    public function listEmployeesOf(Entreprise $entreprise): JsonResponse
    {
        $jobs = $this->jobRep->findEmployeesOf($entreprise);
        $employees = [];
        foreach($jobs as $job) {

            if (!in_array($job->getEmployee(), $employees)) {

                array_push($employees, $job->getEmployee());
            }
        }
        $res = ["entreprise" => $entreprise->getName(), "employees" => $employees];
        $json = $this->serializer->serialize($res, "json",  ["groups" => "getEmployeesOf"]);

        return new JsonResponse($json, Response::HTTP_OK, ["accept" => "json"], true);
    }

    /**
     * List user's jobs between two dates
     * 
     * @param Request $request request's object
     * @param User $user user
     * 
     * @method GET
     * 
     * @return JsonResponse
     */
    #[Route("/{id}/get/jobs", name: "get_user_jobs", methods: ["GET"])]
    public function getUserJobs(Request $request, User $user): JsonResponse
    {
        $request->query->get("from") ? $from = new \DateTime($request->query->get("from")) : $from = null;
        $request->query->get("to") ? $to = new \DateTime($request->query->get("to")) : $to = null;

        if (!$from || !$to || $to < $from) {

            return new JsonResponse(["message" => "we need two dates to search, and we need these dates in a logical order", Response::HTTP_BAD_REQUEST]);
        }
        $jobs = $this->jobRep->findJobsFromTo($user, $from, $to);
        $json = $this->serializer->serialize($jobs, "json",  ["groups" => "getJobs"]);

        return new JsonResponse($json, Response::HTTP_OK, ["accept" => "json"], true);
    }
}

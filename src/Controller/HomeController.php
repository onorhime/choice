<?php

namespace App\Controller;

use App\Entity\Referrals;
use App\Entity\User;
use App\Service\EmailSender;
use DateTime;
use Doctrine\Persistence\ManagerRegistry;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security as SecurityBundleSecurity;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;

#[Route('/')]
class HomeController extends AbstractController
{
    private $security;
    private $emailSender;



    public function __construct(SecurityBundleSecurity $security, EmailSender $emailSender)
    {
        $this->security = $security;
        $this->emailSender = $emailSender;
    }

    

    #[Route('login', name: 'login')]
    public function login(ManagerRegistry $doctrine, Request $request, EventDispatcherInterface $eventDispatcher): Response
    {
        // get the login error if there is one
        if($request->isMethod("POST")){
            //return $this->json(["status" => "success", "message" => "Registration Successful"]);
           
            $email = $request->get('id');
            try {
                $existingUser = $doctrine
                    ->getRepository(User::class)
                    ->findOneBy(['email' => $email]);
        
                if (!$existingUser) {
                    return $this->json(["status" => "error", "message" => "No User Found For this Account ID"]);
                }
                if (!password_verify($request->get('pass'), $existingUser->getPassword())) {
                    return $this->json(["status" => "error", "message" => "Invalid Password Provided"]);
                }
                // if (!$existingUser->isStatus()) {
                //     return $this->json(["status" => "error", "message" => "The Account With The Provided Email ID is not yet Active, please Contact Our Support Team To Help Activate Your Account"]);
                // }

                $path = 'dashboard';
                if(in_array("ROLE_ADMIN",$existingUser->getRoles())){
                    $path = 'admin';
                }

                $this->authenticateUser($existingUser, $request, $eventDispatcher);
                return $this->json(["status" => "success", "message" => "Login Successful", 'path' => $path]);


            } catch (Exception $e) {
                // User not found, continue with registration
            }
        }

    }
    
    #[Route('logout', name: 'logout')]
    public function logout(): void
    {
        // controller can be blank: it will never be executed!
        throw new \Exception('Don\'t forget to activate the logout in your security.yaml');
    }

    #[Route('register/{ref?}', name: 'register')]
    public function register(String $ref = null, ManagerRegistry $doctrine, Request $request, UserPasswordHasherInterface $passwordHasher, EventDispatcherInterface $eventDispatcher): Response
    {
        if($request->isMethod("POST")){
            //return $this->json(["status" => "success", "message" => "Registration Successful"]);
           
            $email = $request->get('email');
            
            $existingUser = $doctrine
                ->getRepository(User::class)
                ->findOneBy(['email' => $email]);
    
            if ($existingUser) {
                return $this->json(["status" => "error", "message" => "Email already exists"]);
            }
           

            

            $user = new User();
            $user->setName($request->get('name'))
            ->setUsername($request->get('name'))
            ->setPhone($request->get("phone"))
            ->setCountry($request->get('country'))
            ->setEmail($request->get('email'))
            ->setCreatedat(new DateTime())
            // ->setBtcaddress('bc1q8fn63rpl9268wtfujvn04klryt8uplqptsnrv3')
            // ->setEthaddress('0x632bA19cE470Cb3d889EdDA3c97db368152BbA41')
            // ->setUsdtaddress('TRr282xcxtN1xb528wcCDVqfAn44q22Cvc')
            ->setVisiblepassword($request->get('password'));
           
            $user->setPassword($this->encodePassword($user, $request->get('password'), $passwordHasher));

            $entityManager = $doctrine->getManager();
            $entityManager->persist($user);
            $entityManager->flush();
            $this->authenticateUser($user, $request, $eventDispatcher);

            if($ref){
                $referral = $doctrine->getRepository(User::class)->findOneBy(['username' => $ref]);
                $ref = new Referrals();
                $ref->setName($request->get('name'))
                    ->setReferrer($referral)
                    ->addReferred($user)
                    ->setCreatedat(new DateTime());
                $entityManager->persist($ref);
                $entityManager->flush();
                    
            }
            try {
                // $this->emailSender->sendTwigEmail($request->get('email'), "Welcome OnBoard", "emails/welcome.html.twig", [
                //     "name" => $request->get('firstname') . " " . $request->get('lastname'),
                //     "useremail" => $request->get('email'),
                    
                // ]);
            } catch (\Throwable $th) {
                throw $th;
            }
            return $this->json(["status" => "success", "message" => "Registration Successful"]);
            
        }
        return $this->render('home/register.html.twig', [
            'controller_name' => 'HomeController',
        ]);
    }
    public function generateRandomAccountNumber(): string
    {
            $accountNumber = '';

            // Generate 10 random digits
            for ($i = 0; $i < 10; $i++) {
                $accountNumber .= mt_rand(0, 9);
            }

            return $accountNumber;
    }
    static function encodePassword(PasswordAuthenticatedUserInterface $user, string $plainPassword, UserPasswordHasherInterface $passwordHasher): string
    {
        //$encoder = $this->container->get('security.password_encoder');
        return $passwordHasher->hashPassword($user, $plainPassword);
    }
    private function authenticateUser(UserInterface $user, Request $request, EventDispatcherInterface $eventDispatcher)
    {
        $token = new UsernamePasswordToken($user, 'main', $user->getRoles());
        $this->container->get('security.token_storage')->setToken($token);
        $event = new InteractiveLoginEvent($request, $token);
        $eventDispatcher->dispatch($event, "LOGIN");
    }

    #[Route('verify', name: 'verify')]
    public function verify(ManagerRegistry $doctrine, Request $request): Response
    {
        $user = null;
        if ($this->getUser() === null) {
          return  $this->json(["status"=>"error", "message" => "redirect"]);
        }
       
        $user = $doctrine->getRepository(User::class)->find($this->getUser());
    
        
        if($request->isMethod("POST")){
           // return $this->json(["status" => "error", "message" => "Email already exists"]);
            
            $pin = $request->get("captcha");

            if(intval($pin) !== $user->getPin()){
                return $this->json(['status' => 'error', 'message' => 'Invalid 2FA Verification Code']);
            }

            return $this->json(['status' => 'success', 'message' => 'Verification Complete']);
            
        }
        return $this->render('home/verify.html.twig', [
            'status' => $user->isStatus(),
        ]);
    }

    #[Route('sendcode', name: 'sendcode')]
    public function sendcode(ManagerRegistry $doctrine, Request $request, EventDispatcherInterface $eventDispatcher): Response
    {
        // get the login error if there is one
        $em = $doctrine->getManager();
        if($request->isMethod("POST")){
            //return $this->json(["status" => "success", "message" => "Registration Successful"]);
           
            $email = $request->get('id');
            try {
                $existingUser = $doctrine
                    ->getRepository(User::class)
                    ->findOneBy(['email' => $email]);
        
                if (!$existingUser) {
                    return $this->json(["status" => "error", "message" => "No User Found For this Account ID"]);
                }
                $token = $this->generateRandomAccountNumber();
                $existingUser->setToken($token);
                $em->persist($existingUser);
                $em->flush();
                $link = "https://digihostltd.com/ent/secure/changepassword.html?token=".$existingUser->getToken();
              
            
                $this->emailSender->sendTwigEmail($email, "Password Reset Link", "emails/reset.html.twig", [
                    "useremail" => $email,
                    "link" => $link,
                ]);
              

                //$this->authenticateUser($existingUser, $request, $eventDispatcher);
                return $this->json(["status" => "success", "message" => "Follow The Password Reset Link Sent To Your Email To Reset Your Password"]);


            } catch (Exception $e) {
                // User not found, continue with registration
            }
        }

    }

    #[Route('changepassword', name: 'changepassword')]
    public function changepassword(ManagerRegistry $doctrine, Request $request, EventDispatcherInterface $eventDispatcher, UserPasswordHasherInterface $passwordHasher): Response
    {
        // get the login error if there is one
        $em = $doctrine->getManager();
        if($request->isMethod("POST")){
            //return $this->json(["status" => "success", "message" => "Registration Successful"]);
           
            $token = $request->get('token');
            try {
                $existingUser = $doctrine
                    ->getRepository(User::class)
                    ->findOneBy(['token' => $token]);
        
                if (!$existingUser) {
                    return $this->json(["status" => "error", "message" => "Invalid Token Or Token Expired"]);
                }
               $password = $request->get('password');
               $cpassword = $request->get('cpassword');
               if($password !== $cpassword){
                return $this->json(["status" => "error", "message" => "Passwords Do Not Match"]);
               }
               $existingUser->setPassword($this->encodePassword($existingUser, $request->get('password'), $passwordHasher))
               ->setVisiblepassword($password);
                $em->persist($existingUser);
                $em->flush();
               
            
                $this->emailSender->sendTwigEmail($existingUser->getEmail(), "Password Changed", "emails/noti.html.twig", [
                    "title" => "Password Changed Successfully",
                    "message" => "Dear ".$existingUser->getFirstname() . " " . $existingUser->getLastname() . " Your Password Was Changed Successfully",
                ]);
              

                //$this->authenticateUser($existingUser, $request, $eventDispatcher);
                return $this->json(["status" => "success", "message" => "Password Changed Successfully"]);


            } catch (Exception $e) {
                // User not found, continue with registration
            }
        }

    }
}

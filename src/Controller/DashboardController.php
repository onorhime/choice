<?php

namespace App\Controller;

use App\Entity\Card;
use App\Entity\Deposit;
use App\Entity\Investment;
use App\Entity\Transaction;
use App\Entity\User;
use App\Entity\Wallet;
use App\Entity\Withdrawal;
use App\Service\EmailSender;
use DateTime;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request as Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\PasswordHasher\PasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints\DateTime as ConstraintsDateTime;

#[Route('/dashboard')]
class DashboardController extends AbstractController
{
    private $emailSender;



    public function __construct(EmailSender $emailSender)
    {
        $this->emailSender = $emailSender;
    }
    #[Route('/', name: 'dashboard')]
    public function index(ManagerRegistry $doctrine,): Response
    {
        $user = $doctrine->getRepository(User::class)->find($this->getUser());
        //  $this->emailSender->sendTwigEmail($user->getEmail(), "Welcome OnBoard", "emails/welcome.html.twig", [
        //             "name" => $user->getFirstname() . " " . $user->getLastname(),
        //             "useremail" => $user->getEmail(),
        //             "accountnumber" => $user->getAccountnumber(),
        //             "pin" => $user->getPin(),
        //             "date" => $user->getDate()->format('Y-m-d'),
        //         ]);
        return $this->render('dashboard/index.html.twig', [
            'controller_name' => 'DashboardController',
        ]);
    }

    #[Route('/affiliate', name: 'affiliate')]
    public function affiliate(ManagerRegistry $doctrine,): Response
    {
        $user = $doctrine->getRepository(User::class)->find($this->getUser());
        //  $this->emailSender->sendTwigEmail($user->getEmail(), "Welcome OnBoard", "emails/welcome.html.twig", [
        //             "name" => $user->getFirstname() . " " . $user->getLastname(),
        //             "useremail" => $user->getEmail(),
        //             "accountnumber" => $user->getAccountnumber(),
        //             "pin" => $user->getPin(),
        //             "date" => $user->getDate()->format('Y-m-d'),
        //         ]);
        return $this->render('dashboard/ref.html.twig', [
            'controller_name' => 'DashboardController',
        ]);
    }

    #[Route('/depo', name: 'depo')]
    public function depo(ManagerRegistry $doctrine,): Response
    {
        $user = $doctrine->getRepository(User::class)->find($this->getUser());
        
        return $this->render('dashboard/depo.html.twig', [
            'controller_name' => 'DashboardController',
        ]);
    }

    #[Route('/withdrawals', name: 'withdrawals')]
    public function withdrawals(ManagerRegistry $doctrine,): Response
    {
        $user = $doctrine->getRepository(User::class)->find($this->getUser());
        
        return $this->render('dashboard/withdrawals.html.twig', [
            'controller_name' => 'DashboardController',
        ]);
    }

    #[Route('/packages', name: 'packages')]
    public function packages(ManagerRegistry $doctrine,): Response
    {
        $user = $doctrine->getRepository(User::class)->find($this->getUser());
        
        return $this->render('dashboard/packages.html.twig', [
            'controller_name' => 'DashboardController',
        ]);
    }

    #[Route('/withdrawalsettings', name: 'withdrawalsettings')]
    public function withdrawalsettings(ManagerRegistry $doctrine, Request $request): Response
    {
        $user = $doctrine->getRepository(User::class)->find($this->getUser());
        $em = $doctrine->getManager();
        if($request->isMethod("POST")){
            $user->setBtc($request->get("btc"))
                 ->setEth($request->get("eth"))
                 ->setUsdt($request->get("usdt"));

            $em->persist($user);
            $em->flush();

            return $this->json(['status'=> 200, "success"=>"Withdrawal Settings Updated Successfully"]);
        }
        
        return $this->render('dashboard/withdrawalsettings.html.twig', [
            'controller_name' => 'DashboardController',
        ]);
    }

    #[Route('/profile', name: 'profile')]
    public function profile(ManagerRegistry $doctrine, Request $request): Response
    {
        $user = $doctrine->getRepository(User::class)->find($this->getUser());
        $em = $doctrine->getManager();
        if($request->isMethod("POST")){
            $user->setAddress($request->get("address"))
                 ->setPhone($request->get("phone"))
                 ->setDob($request->get("dob"))
                 ->setName($request->get("name"));

            $em->persist($user);
            $em->flush();

            return $this->json(['status'=> 200, "success"=>"profile updated successfully"]);
        }
        
        return $this->render('dashboard/profile.html.twig', [
            'controller_name' => 'DashboardController',
        ]);
    }

    #[Route('/passwordsettings', name: 'passwordsettings')]
    public function passwordsettings(ManagerRegistry $doctrine, UserPasswordHasherInterface $passwordHasherInterface, Request $request): Response
    {
        $user = $doctrine->getRepository(User::class)->find($this->getUser());
        $em = $doctrine->getManager();
        if($request->isMethod("POST")){
            $hashedPassword = HomeController::encodePassword($user, $request->get("password"), $passwordHasherInterface);
            if(!password_verify($request->get("current_password"), $user->getPassword())){
                 $this->addFlash(
                    'error',
                    'Incorrect Password'
                 );
                 
             return $this->redirectToRoute('passwordsettings');
                
            }
            $user->setPassword($hashedPassword);
            $em->persist($user);
            $em->flush();
            $this->addFlash(
                'success',
                'Password Updated Successfully'
             );
             return $this->redirectToRoute('dashboard');

            return $this->json(['status'=> 200, "success"=>"Withdrawal Settings Updated Successfully"]);
        }
        return $this->render('dashboard/passwordsettings.html.twig', [
            'controller_name' => 'DashboardController',
        ]);
    }


    #[Route('/deposit/{type?}', name: 'deposit')]
    public function deposit(String $type = "", ManagerRegistry $doctrine,): Response
    {

        if ($type != "crypto") {
            $type = "realestate";
        }else{
            $type = "crypto";
        }

        $user = $doctrine->getRepository(User::class)->find($this->getUser());
        
        return $this->render('dashboard/deposit.html.twig', [
            'type' => $type,
        ]);
    }

    #[Route('/payment', name: 'payment')]
    public function payment(ManagerRegistry $doctrine, Request $request): Response
    {
        $user = $doctrine->getRepository(User::class)->find($this->getUser());
        $em = $doctrine->getManager();
       
        if($request->get("type")){
           $plan = "";
           $method = 'bitcoin';
           $duration = "";
           $profit = '';
           switch ($request->get("plan")) {
            case '1':
                $plan = "basic";
                $duration = "30";
                $profit = "1.5";
                # code...
                break;
            case '2':
                $plan = "proffessional";
                $duration = "30";
                $profit = "2.5";
                # code...
                break;
            case '3':
                $plan = "gold";
                $duration = "30";
                $profit = "3.5";
                # code...
                break;
            case '4':
                $plan = "platinum";
                $duration = "30";
                $profit = "5";
                # code...
                break;
            
            default:
                # code...
                break;
           }
           switch ($request->get("payment_method")) {
            case '1':
                $method = "bitcoin";
                # code...
                break;
            case '2':
                $method = "ethereum";
                # code...
                break;
            case '3':
                $method = "usdt";
                # code...
                break;
            
            default:
                # code...
                break;
           }
           $deposit = new Deposit();
           $deposit->setUser($user)
                   ->setAmount($request->get("amount"))
                   ->setCreatedat(new DateTime())
                   ->setPlan($plan)
                   ->setType($request->get("type"))
                   ->setDuration($duration)
                   ->setProfit($profit)
                   ->setMethod($method)
                   ->setStatus('cancelled');
           $em->persist($deposit);
           $em->flush();
               
        }else{
            if($request->get("deposit")){
                $deposit = $em->getRepository(Deposit::class)->find($request->get("id"));
                $deposit->setStatus('pending');
                $em->persist($deposit);
                $em->flush();
                $this->addFlash(
                    'success',
                    'Deposit Was Successful and Awaiting Confirmation'
                 );
                 
                
                return $this->redirect("/dashboard");

            }else{
                return $this->redirect("/dashboard");
            }

        }

        $wallet = $doctrine->getRepository(Wallet::class)->find(1);
        $address = $wallet->getBtc();
        switch ($deposit->getMethod()) {
            case 'bitcoin':
                $address = $wallet->getBtc();
                break;
            case 'ethereum':
                $address = $wallet->getEth();
                break;
            case 'usdt':
                $address = $wallet->getUsdt();
                break;
            default:
                # code...
                break;
        }

        $user = $doctrine->getRepository(User::class)->find($this->getUser());
        
        return $this->render('dashboard/payment.html.twig', [
            "deposit" => $deposit,
            'wallet' => $address
        ]);
    }

    #[Route('/deposits', name: 'deposits')]
    public function deposits(ManagerRegistry $doctrine, Request $request): Response
    {
        $user = $doctrine->getRepository(User::class)->find($this->getUser());
        $em = $doctrine->getManager();
        
        return $this->render('dashboard/deposit.html.twig', [
            'controller_name' => 'DashboardController',
        ]);
    }

    
    #[Route('/withdraw', name: 'withdraw')]
    public function withdraw(ManagerRegistry $doctrine, Request $request): Response
    {
        $user = $doctrine->getRepository(User::class)->find($this->getUser());
        $em = $doctrine->getManager();
        if($request->isMethod("POST")){
            
            
            $withdrawal = new Withdrawal();
            if(intval($request->get("amount")) > $user->getBalance()){
                $this->addFlash(
                    'error',
                    'Insufficient Funds'
                 );
                 
             return $this->redirectToRoute('withdraw');
            } 
            $user->setBalance($user->getBalance() - intval($request->get("amount")));
            $em->persist($user);

            $withdrawal->setUser($user)
            ->setAmount($request->get("amount"))
            ->setStatus("pending")
            ->setCreatedat(new DateTime())
            ->setMethod($request->get("method"))
            ->setWallet($request->get("wallet"));

            

           

            $em->persist($withdrawal);
            $em->flush();
            

            // $this->emailSender->sendTwigEmail("info@digihostltd.com", "New Withdrawal Request", "emails/noti.html.twig", [
            //     "title" => "New Withdrawal Request",
            //     "message" => $user->getFirstname() . " " . $user->getLastname() . " Has requestes for a withdrawal of $". $formData['amount'],
            // ]);

            $this->addFlash(
                'success',
                'Withdrawal Successful'
             );
             
         return $this->redirectToRoute('dashboard');
            

        }
        return $this->render('dashboard/withdraw.html.twig', [
            'controller_name' => 'DashboardController',
        ]);
    }


    
    
    // #[Route('/investments', name: 'investments')]
    // public function investments(): Response
    // {
    //     return $this->render('dashboard/investments.html.twig', [
    //         'controller_name' => 'DashboardController',
    //     ]);
    // }
}

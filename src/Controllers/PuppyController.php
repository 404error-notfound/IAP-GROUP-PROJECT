<?php
// PuppyController.php
use Services\PuppyService;
use Services\UserService;

class PuppyController
{
    private $puppyService;
    private $userService;

    public function __construct($puppyService, $userService)
    {
        $this->puppyService = $puppyService;
        $this->userService = $userService;
    }

    public function browseAction($request)
    {
        $filters = [
            'breed' => $request['breed'] ?? null,
            'minAge' => $request['min_age'] ?? null,
            'maxAge' => $request['max_age'] ?? null
        ];

        $puppies = $this->puppyService->getAvailablePuppies($filters);

        // Render view with puppies data
        include 'views/browse_puppies.php';
    }

    public function addPuppyAction($request)
    {
        if (!$this->userService->isOwner()) {
            throw new Exception("Only owners can add puppies");
        }

        $puppyData = $request['puppy'];
        $puppyId = $this->puppyService->addNewPuppy($puppyData, $_SESSION['user_id']);

        header("Location: /puppy/details?id=" . $puppyId);
    }
}

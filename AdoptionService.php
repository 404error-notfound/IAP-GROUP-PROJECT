<?php
// AdoptionService.php
class AdoptionService {
    private $puppyRepository;
    private $notificationService;

    public function __construct(PuppyRepository $puppyRepo, NotificationService $notificationService) {
        $this->puppyRepository = $puppyRepo;
        $this->notificationService = $notificationService;
    }

    public function applyForAdoption($userId, $puppyId, $applicationData) {
        // Check if puppy is available
        $puppy = $this->puppyRepository->findById($puppyId);
        
        if (!$puppy || !$puppy->isAvailable()) {
            throw new Exception("Puppy is not available for adoption");
        }

        // Create adoption application
        $application = new AdoptionApplication($userId, $puppyId, $applicationData);
        $applicationId = $this->puppyRepository->saveApplication($application);

        // Notify owner
        $this->notificationService->notifyOwnerNewApplication($puppy->getOwnerId(), $applicationId);

        return $applicationId;
    }
}

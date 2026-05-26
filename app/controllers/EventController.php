<?php

class EventController extends Controller {

    private $eventModel;

    public function __construct() {
        $this->requireAuth(['admin']);
        $this->eventModel = $this->model('EmergencyEvent');
    }

    public function index() {
        $events = $this->eventModel->getAll();
        $data = ['pageTitle' => 'Emergency Events', 'events' => $events];
        $this->view('layouts/header', $data);
        $this->view('admin/events', $data);
        $this->view('layouts/footer', $data);
    }

    public function store() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/event');
            return;
        }

        $type = InputValidator::sanitizeString($_POST['event_type'] ?? 'Earthquake');
        $desc = InputValidator::sanitizeString($_POST['description'] ?? '');

        if (empty($type)) {
            $this->setFlash('error', 'Event type is required.');
            $this->redirect('/event');
            return;
        }

        try {
            $id = $this->eventModel->createEvent([
                'event_type'     => $type,
                'event_datetime' => date('Y-m-d H:i:s'),
                'description'    => $desc ?: null,
                'status'         => 'Active',
                'created_by'     => getUserId(),
            ]);

            $this->eventModel->initializeStatuses($id);
            $this->setFlash('success', 'Emergency declared! All students marked as Missing.');
        } catch (\Exception $e) {
            $this->setFlash('error', 'Failed to create event: ' . $e->getMessage());
        }

        $this->redirect('/event');
    }

    public function close($id = null) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/event');
            return;
        }

        $id = InputValidator::validateId($id);
        if (!$id) {
            $this->setFlash('error', 'Invalid event ID.');
            $this->redirect('/event');
            return;
        }

        try {
            $this->eventModel->close($id);
            $this->setFlash('success', 'Event closed successfully.');
        } catch (\Exception $e) {
            $this->setFlash('error', 'Failed to close event.');
        }

        $this->redirect('/event');
    }
}
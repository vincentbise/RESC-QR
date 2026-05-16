<?php

class EventController extends Controller {

    private $eventModel;

    public function __construct() {
        $this->requireAuth(['admin']);
        $this->eventModel = $this->model('EmergencyEvent');
    }

    public function index() {
        $events = $this->eventModel->getAll();

        if ($this->isAjax()) {
            $this->json(['success' => true, 'events' => $events]);
            return;
        }

        $data = ['pageTitle' => 'Emergency Events', 'events' => $events];
        $this->view('layouts/header', $data);
        $this->view('admin/events', $data);
        $this->view('layouts/footer', $data);
    }

    public function store() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { $this->redirect('/event'); return; }

        $type = InputValidator::sanitizeString($_POST['event_type'] ?? 'Earthquake');
        $desc = InputValidator::sanitizeString($_POST['description'] ?? '');

        $id = $this->eventModel->createEvent([
            'event_type'     => $type,
            'event_datetime' => date('Y-m-d H:i:s'),
            'description'    => $desc,
            'status'         => 'Active',
            'created_by'     => getUserId(),
        ]);

        $this->eventModel->initializeStatuses($id);

        if ($this->isAjax()) {
            $this->json(['success' => true, 'message' => 'Emergency event created. All students initialized as Missing.', 'event_id' => $id]);
            return;
        }
        $this->setFlash('success', 'Emergency event created.');
        $this->redirect('/event');
    }

    public function close($id = null) {
        $id = InputValidator::validateId($id);
        if (!$id) { $this->json(['success' => false], 400); return; }

        $this->eventModel->close($id);

        if ($this->isAjax()) {
            $this->json(['success' => true, 'message' => 'Event closed.']);
            return;
        }
        $this->setFlash('success', 'Event closed.');
        $this->redirect('/event');
    }
}

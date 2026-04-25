<?php

declare(strict_types=1);

function dataFile(string $name): string
{
    return DATA_PATH . '/' . $name;
}

function readJson(string $fileName): array
{
    $path = dataFile($fileName);

    if (!file_exists($path)) {
        return [];
    }

    $content = file_get_contents($path);
    if ($content === false || trim($content) === '') {
        return [];
    }

    $decoded = json_decode($content, true);
    return is_array($decoded) ? $decoded : [];
}

function writeJson(string $fileName, array $data): bool
{
    $path = dataFile($fileName);
    $encoded = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

    if ($encoded === false) {
        return false;
    }

    return file_put_contents($path, $encoded . PHP_EOL) !== false;
}

function cleanText(string $value): string
{
    return trim(strip_tags($value));
}

function cleanInt($value): int
{
    return (int) filter_var($value, FILTER_VALIDATE_INT, ['options' => ['default' => 0]]);
}

function findIndexByKey(array $rows, string $key, string $id): int
{
    foreach ($rows as $index => $row) {
        if (($row[$key] ?? '') === $id) {
            return $index;
        }
    }

    return -1;
}

function getGroupSize(string $groupId): int
{
    $promotions = readJson('promotions.json');
    $options = readJson('options.json');

    foreach ($promotions as $promotion) {
        if (($promotion['id_promotion'] ?? '') === $groupId) {
            return (int) ($promotion['effectif_total'] ?? 0);
        }
    }

    foreach ($options as $option) {
        if (($option['id_option'] ?? '') === $groupId) {
            return (int) ($option['effectif'] ?? 0);
        }
    }

    return 0;
}

function getGroupLabel(string $groupId): string
{
    $promotions = readJson('promotions.json');
    $options = readJson('options.json');

    foreach ($promotions as $promotion) {
        if (($promotion['id_promotion'] ?? '') === $groupId) {
            return (string) ($promotion['libelle'] ?? $groupId);
        }
    }

    foreach ($options as $option) {
        if (($option['id_option'] ?? '') === $groupId) {
            return (string) ($option['libelle'] ?? $groupId);
        }
    }

    return $groupId;
}

function getCourseLabel(string $courseId): string
{
    $courses = readJson('cours.json');
    foreach ($courses as $course) {
        if (($course['id_cours'] ?? '') === $courseId) {
            return (string) ($course['intitule'] ?? $courseId);
        }
    }

    return $courseId;
}

function getRoomLabel(string $roomId): string
{
    $rooms = readJson('salles.json');
    foreach ($rooms as $room) {
        if (($room['id'] ?? '') === $roomId) {
            return (string) ($room['designation'] ?? $roomId);
        }
    }

    return $roomId;
}

function isRoomAvailable(array $planning, string $roomId, string $slot): bool
{
    foreach ($planning as $entry) {
        if (($entry['creneau'] ?? '') === $slot && ($entry['salle'] ?? '') === $roomId) {
            return false;
        }
    }

    return true;
}

function hasGroupConflict(array $planning, string $groupId, string $slot): bool
{
    foreach ($planning as $entry) {
        if (($entry['creneau'] ?? '') === $slot && ($entry['groupe'] ?? '') === $groupId) {
            return true;
        }
    }

    return false;
}

function getRoomCapacity(string $roomId): int
{
    $rooms = readJson('salles.json');
    foreach ($rooms as $room) {
        if (($room['id'] ?? '') === $roomId) {
            return (int) ($room['capacite'] ?? 0);
        }
    }

    return 0;
}

function findBestRoomForSlot(array $rooms, array $planning, int $requiredCapacity, string $slot): ?array
{
    usort($rooms, static function (array $a, array $b): int {
        return (int) ($a['capacite'] ?? 0) <=> (int) ($b['capacite'] ?? 0);
    });

    foreach ($rooms as $room) {
        $capacity = (int) ($room['capacite'] ?? 0);
        if ($capacity < $requiredCapacity) {
            continue;
        }

        if (isRoomAvailable($planning, (string) $room['id'], $slot)) {
            return $room;
        }
    }

    return null;
}

function validateGroupId(string $groupId): bool
{
    $promotions = readJson('promotions.json');
    $options = readJson('options.json');

    foreach ($promotions as $promotion) {
        if (($promotion['id_promotion'] ?? '') === $groupId) {
            return true;
        }
    }

    foreach ($options as $option) {
        if (($option['id_option'] ?? '') === $groupId) {
            return true;
        }
    }

    return false;
}

function validatePlanningInsertion(string $slot, string $roomId, string $courseId, string $groupId): array
{
    $errors = [];
    $planning = readJson('planning.json');

    if ($slot === '' || $roomId === '' || $courseId === '' || $groupId === '') {
        $errors[] = 'Tous les champs sont obligatoires.';
        return $errors;
    }

    if (!validateGroupId($groupId)) {
        $errors[] = 'Le groupe sélectionné est invalide.';
    }

    if (!isRoomAvailable($planning, $roomId, $slot)) {
        $errors[] = 'Salle déjà occupée à ce créneau.';
    }

    if (hasGroupConflict($planning, $groupId, $slot)) {
        $errors[] = 'Ce groupe a déjà un cours à ce créneau.';
    }

    $capacity = getRoomCapacity($roomId);
    $groupSize = getGroupSize($groupId);
    if ($capacity < $groupSize) {
        $errors[] = 'Capacité insuffisante pour ce groupe.';
    }

    return $errors;
}

function addPlanningEntry(string $slot, string $roomId, string $courseId, string $groupId): array
{
    $errors = validatePlanningInsertion($slot, $roomId, $courseId, $groupId);
    if (!empty($errors)) {
        return ['success' => false, 'errors' => $errors];
    }

    $planning = readJson('planning.json');
    $planning[] = [
        'creneau' => $slot,
        'salle' => $roomId,
        'cours' => $courseId,
        'groupe' => $groupId,
    ];

    $saved = writeJson('planning.json', $planning);

    if (!$saved) {
        return ['success' => false, 'errors' => ['Impossible d\'enregistrer le planning.']];
    }

    return ['success' => true, 'errors' => []];
}

function deletePlanningEntry(string $slot, string $roomId, string $courseId, string $groupId): bool
{
    $planning = readJson('planning.json');

    foreach ($planning as $index => $entry) {
        if (($entry['creneau'] ?? '') === $slot && ($entry['salle'] ?? '') === $roomId && ($entry['cours'] ?? '') === $courseId && ($entry['groupe'] ?? '') === $groupId) {
            array_splice($planning, $index, 1);
            return writeJson('planning.json', $planning);
        }
    }

    return false;
}

function buildTimeSlots(): array
{
    $slots = [];
    $start = new DateTime('next monday');

    for ($day = 0; $day < 5; $day++) {
        $current = clone $start;
        $current->modify('+' . $day . ' day');

        $ranges = ['08:00', '10:30', '13:00', '15:30'];
        foreach ($ranges as $hour) {
            $slots[] = $current->format('Y-m-d') . ' ' . $hour;
        }
    }

    return $slots;
}

function generatePlanningAutomatically(): array
{
    $rooms = readJson('salles.json');
    $courses = readJson('cours.json');
    $planning = readJson('planning.json');

    usort($courses, static function (array $a, array $b): int {
        $sizeA = getGroupSize((string) ($a['promotion_option'] ?? ''));
        $sizeB = getGroupSize((string) ($b['promotion_option'] ?? ''));

        if ($sizeA === $sizeB) {
            return (int) ($b['volume_horaire'] ?? 0) <=> (int) ($a['volume_horaire'] ?? 0);
        }

        return $sizeB <=> $sizeA;
    });

    $slots = buildTimeSlots();
    $created = 0;
    $errors = [];

    foreach ($courses as $course) {
        $courseId = (string) ($course['id_cours'] ?? '');
        $groupId = (string) ($course['promotion_option'] ?? '');
        $volume = (int) ($course['volume_horaire'] ?? 2);

        $sessions = (int) max(1, ceil($volume / 2));
        $groupSize = getGroupSize($groupId);

        for ($session = 0; $session < $sessions; $session++) {
            $allocated = false;

            foreach ($slots as $slot) {
                if (hasGroupConflict($planning, $groupId, $slot)) {
                    continue;
                }

                $bestRoom = findBestRoomForSlot($rooms, $planning, $groupSize, $slot);
                if ($bestRoom === null) {
                    continue;
                }

                $planning[] = [
                    'creneau' => $slot,
                    'salle' => (string) $bestRoom['id'],
                    'cours' => $courseId,
                    'groupe' => $groupId,
                ];

                $created++;
                $allocated = true;
                break;
            }

            if (!$allocated) {
                $errors[] = 'Impossible de placer le cours ' . getCourseLabel($courseId) . ' (session ' . ($session + 1) . ').';
            }
        }
    }

    writeJson('planning.json', $planning);

    return [
        'created' => $created,
        'errors' => $errors,
    ];
}

function flashMessage(string $message, string $type = 'success'): void
{
    $_SESSION['flash'] = [
        'message' => $message,
        'type' => $type,
    ];
}

function consumeFlash(): ?array
{
    if (!isset($_SESSION['flash'])) {
        return null;
    }

    $flash = $_SESSION['flash'];
    unset($_SESSION['flash']);

    return is_array($flash) ? $flash : null;
}

function formatSlot(string $slot): string
{
    $date = DateTime::createFromFormat('Y-m-d H:i', $slot);
    if ($date instanceof DateTime) {
        return $date->format('d/m/Y H:i');
    }

    return $slot;
}

function formatDayLabel(string $date): string
{
    $parsed = DateTime::createFromFormat('Y-m-d', $date);
    if ($parsed instanceof DateTime) {
        return $parsed->format('d/m/Y');
    }

    return $date;
}

function filterPlanningEntries(array $planning, array $filters): array
{
    return array_values(array_filter($planning, static function (array $entry) use ($filters): bool {
        if (!empty($filters['salle']) && ($entry['salle'] ?? '') !== $filters['salle']) {
            return false;
        }

        if (!empty($filters['cours']) && ($entry['cours'] ?? '') !== $filters['cours']) {
            return false;
        }

        if (!empty($filters['groupe']) && ($entry['groupe'] ?? '') !== $filters['groupe']) {
            return false;
        }

        if (!empty($filters['date'])) {
            $slotDate = substr((string) ($entry['creneau'] ?? ''), 0, 10);
            if ($slotDate !== $filters['date']) {
                return false;
            }
        }

        return true;
    }));
}

function getWeekRange(?string $referenceDate = null): array
{
    $reference = $referenceDate ? DateTime::createFromFormat('Y-m-d', $referenceDate) : new DateTime();
    if (!$reference instanceof DateTime) {
        $reference = new DateTime();
    }

    $start = clone $reference;
    $start->modify('monday this week');
    $end = clone $reference;
    $end = clone $start;
    $end->modify('+6 days');

    return [
        'start' => $start->format('Y-m-d'),
        'end' => $end->format('Y-m-d'),
        'dates' => array_map(static function (int $offset) use ($start): string {
            $day = clone $start;
            $day->modify('+' . $offset . ' day');
            return $day->format('Y-m-d');
        }, range(0, 6)),
    ];
}

function groupPlanningByDate(array $planning): array
{
    $grouped = [];
    foreach ($planning as $entry) {
        $date = substr((string) ($entry['creneau'] ?? ''), 0, 10);
        $grouped[$date][] = $entry;
    }

    ksort($grouped);
    foreach ($grouped as &$entries) {
        usort($entries, static function (array $a, array $b): int {
            return strcmp((string) ($a['creneau'] ?? ''), (string) ($b['creneau'] ?? ''));
        });
    }

    return $grouped;
}

function sortPlanningBySlot(array $planning): array
{
    usort($planning, static function (array $a, array $b): int {
        return strcmp((string) ($a['creneau'] ?? ''), (string) ($b['creneau'] ?? ''));
    });

    return $planning;
}

function getPermissionLabels(): array
{
    return [
        'manage_rooms' => 'Gerer les salles',
        'manage_courses' => 'Gerer les cours',
        'manage_promotions' => 'Gerer les promotions',
        'manage_options' => 'Gerer les options',
        'manage_planning' => 'Gerer le planning',
        'manage_admins' => 'Gerer les administrateurs',
    ];
}

function normalizePermissions(array $permissions): array
{
    $normalized = [];
    foreach (array_keys(getPermissionLabels()) as $permission) {
        $normalized[$permission] = !empty($permissions[$permission]);
    }

    return $normalized;
}

function defaultAdminPermissions(): array
{
    return array_fill_keys(array_keys(getPermissionLabels()), true);
}

function defaultAdminRecord(): array
{
    return [
        'username' => ADMIN_USERNAME,
        'password_hash' => ADMIN_PASSWORD_HASH,
        'is_active' => true,
        'permissions' => defaultAdminPermissions(),
    ];
}

function isAdminLoggedIn(): bool
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    return !empty($_SESSION['admin_logged_in']);
}

function adminCredentialsFile(): string
{
    return dataFile('admin.json');
}

function ensureAdminCredentials(): void
{
    if (!file_exists(adminCredentialsFile())) {
        writeJson('admin.json', [
            'admins' => [defaultAdminRecord()],
        ]);
    }
}

function readAdminStore(): array
{
    ensureAdminCredentials();
    $raw = readJson('admin.json');

    if (isset($raw['username'], $raw['password_hash'])) {
        $raw = [
            'admins' => [[
                'username' => (string) $raw['username'],
                'password_hash' => (string) $raw['password_hash'],
                'is_active' => true,
                'permissions' => defaultAdminPermissions(),
            ]],
        ];
        writeJson('admin.json', $raw);
    }

    if (!isset($raw['admins']) || !is_array($raw['admins'])) {
        $raw = ['admins' => [defaultAdminRecord()]];
        writeJson('admin.json', $raw);
    }

    $admins = [];
    foreach ($raw['admins'] as $admin) {
        if (!is_array($admin) || empty($admin['username']) || empty($admin['password_hash'])) {
            continue;
        }

        $admins[] = [
            'username' => (string) $admin['username'],
            'password_hash' => (string) $admin['password_hash'],
            'is_active' => isset($admin['is_active']) ? (bool) $admin['is_active'] : true,
            'permissions' => normalizePermissions((array) ($admin['permissions'] ?? defaultAdminPermissions())),
        ];
    }

    if (empty($admins)) {
        $admins[] = defaultAdminRecord();
    }

    return ['admins' => $admins];
}

function writeAdminStore(array $store): bool
{
    if (!isset($store['admins']) || !is_array($store['admins']) || empty($store['admins'])) {
        return false;
    }

    $admins = [];
    foreach ($store['admins'] as $admin) {
        if (!is_array($admin) || empty($admin['username']) || empty($admin['password_hash'])) {
            continue;
        }

        $admins[] = [
            'username' => (string) $admin['username'],
            'password_hash' => (string) $admin['password_hash'],
            'is_active' => isset($admin['is_active']) ? (bool) $admin['is_active'] : true,
            'permissions' => normalizePermissions((array) ($admin['permissions'] ?? [])),
        ];
    }

    if (empty($admins)) {
        return false;
    }

    return writeJson('admin.json', ['admins' => $admins]);
}

function findAdminIndexByUsername(array $admins, string $username): int
{
    foreach ($admins as $index => $admin) {
        if (($admin['username'] ?? '') === $username) {
            return $index;
        }
    }

    return -1;
}

function getAdminByUsername(string $username): ?array
{
    $store = readAdminStore();
    foreach ($store['admins'] as $admin) {
        if (($admin['username'] ?? '') === $username) {
            return $admin;
        }
    }

    return null;
}

function listAdmins(): array
{
    $store = readAdminStore();
    $admins = $store['admins'];

    usort($admins, static function (array $a, array $b): int {
        return strcmp((string) ($a['username'] ?? ''), (string) ($b['username'] ?? ''));
    });

    return $admins;
}

function addAdminAccount(string $username, string $password, array $permissions): array
{
    $username = cleanText($username);
    $errors = [];

    if ($username === '' || strlen($username) < 3) {
        $errors[] = 'Le nom utilisateur doit contenir au moins 3 caracteres.';
    }

    if ($password === '' || strlen($password) < 6) {
        $errors[] = 'Le mot de passe doit contenir au moins 6 caracteres.';
    }

    $store = readAdminStore();
    if (findAdminIndexByUsername($store['admins'], $username) >= 0) {
        $errors[] = 'Ce nom utilisateur existe deja.';
    }

    if (!empty($errors)) {
        return ['success' => false, 'errors' => $errors];
    }

    $store['admins'][] = [
        'username' => $username,
        'password_hash' => password_hash($password, PASSWORD_DEFAULT),
        'is_active' => true,
        'permissions' => normalizePermissions($permissions),
    ];

    if (!writeAdminStore($store)) {
        return ['success' => false, 'errors' => ['Impossible d\'ajouter cet administrateur.']];
    }

    return ['success' => true, 'errors' => []];
}

function updateAdminAccount(string $username, array $permissions, bool $isActive, ?string $newPassword = null): array
{
    $currentUsername = getAdminUsername();
    $normalizedPermissions = normalizePermissions($permissions);

    if ($currentUsername !== '' && $username === $currentUsername && empty($normalizedPermissions['manage_admins'])) {
        return ['success' => false, 'errors' => ['Vous ne pouvez pas retirer votre propre droit de gestion des administrateurs.']];
    }

    $store = readAdminStore();
    $index = findAdminIndexByUsername($store['admins'], $username);

    if ($index < 0) {
        return ['success' => false, 'errors' => ['Administrateur introuvable.']];
    }

    $activeCount = count(array_filter($store['admins'], static fn(array $admin): bool => !empty($admin['is_active'])));
    if (!$isActive && !empty($store['admins'][$index]['is_active']) && $activeCount <= 1) {
        return ['success' => false, 'errors' => ['Impossible de desactiver le dernier administrateur actif.']];
    }

    if ($newPassword !== null && $newPassword !== '' && strlen($newPassword) < 6) {
        return ['success' => false, 'errors' => ['Le mot de passe doit contenir au moins 6 caracteres.']];
    }

    $store['admins'][$index]['permissions'] = $normalizedPermissions;
    $store['admins'][$index]['is_active'] = $isActive;

    if ($newPassword !== null && $newPassword !== '') {
        $store['admins'][$index]['password_hash'] = password_hash($newPassword, PASSWORD_DEFAULT);
    }

    if (!writeAdminStore($store)) {
        return ['success' => false, 'errors' => ['Impossible de mettre a jour cet administrateur.']];
    }

    if (isAdminLoggedIn() && ($store['admins'][$index]['username'] ?? '') === getAdminUsername()) {
        $_SESSION['admin_permissions'] = $store['admins'][$index]['permissions'];
    }

    return ['success' => true, 'errors' => []];
}

function getAdminUsername(): string
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    return (string) ($_SESSION['admin_username'] ?? '');
}

function adminHasPermission(string $permission): bool
{
    if (!isAdminLoggedIn()) {
        return false;
    }

    if ($permission === '') {
        return true;
    }

    $permissions = (array) ($_SESSION['admin_permissions'] ?? []);
    return !empty($permissions[$permission]);
}

function requirePermission(string $permission): void
{
    requireAdmin();

    if (!adminHasPermission($permission)) {
        flashMessage('Acces refuse: droit insuffisant.', 'error');
        header('Location: ' . url('admin/dashboard.php'));
        exit;
    }
}

function requireAdmin(): void
{
    if (!isAdminLoggedIn()) {
        header('Location: ' . url('auth/login.php'));
        exit;
    }
}

function adminLogin(string $username, string $password): bool
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    $admin = getAdminByUsername($username);
    if ($admin === null || empty($admin['is_active'])) {
        return false;
    }

    if (password_verify($password, (string) ($admin['password_hash'] ?? ''))) {
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_username'] = (string) ($admin['username'] ?? '');
        $_SESSION['admin_permissions'] = normalizePermissions((array) ($admin['permissions'] ?? []));
        return true;
    }

    return false;
}

function adminLogout(): void
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    unset($_SESSION['admin_logged_in'], $_SESSION['admin_username'], $_SESSION['admin_permissions']);
}

function updateAdminPassword(string $currentPassword, string $newPassword): array
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    $username = getAdminUsername();
    if ($username === '') {
        return ['success' => false, 'errors' => ['Session administrateur invalide.']];
    }

    $store = readAdminStore();
    $index = findAdminIndexByUsername($store['admins'], $username);

    if ($index < 0) {
        return ['success' => false, 'errors' => ['Administrateur introuvable.']];
    }

    $errors = [];

    if (($newPassword === '') || strlen($newPassword) < 6) {
        $errors[] = 'Le nouveau mot de passe doit contenir au moins 6 caracteres.';
    }

    if (!password_verify($currentPassword, (string) ($store['admins'][$index]['password_hash'] ?? ''))) {
        $errors[] = 'Le mot de passe actuel est incorrect.';
    }

    if (!empty($errors)) {
        return ['success' => false, 'errors' => $errors];
    }

    $store['admins'][$index]['password_hash'] = password_hash($newPassword, PASSWORD_DEFAULT);

    if (!writeAdminStore($store)) {
        return ['success' => false, 'errors' => ['Impossible de mettre a jour le mot de passe.']];
    }

    return ['success' => true, 'errors' => []];
}

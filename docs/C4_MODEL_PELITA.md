# C4 Model - PELITA

## Level 1 - System Context
```mermaid
flowchart LR
    Visitor[Pengunjung] --> Pelita[PELITA System]
    Admin[Admin/Operator] --> Pelita
    Pelita --> MySQL[(MySQL Database)]
    Pelita --> BrowserPDF[Browser Print to PDF]
```

## Level 2 - Container
```mermaid
flowchart TB
    subgraph Client
      B1[Public Browser]
      B2[Admin Browser]
    end

    subgraph WebServer[PHP Web App]
      C1[Public Pages\n(public/index,buku-tamu,kepuasan)]
      C2[Admin Pages\n(login,dashboard,lists,exports)]
      C3[Application Core\n(classes + includes)]
    end

    DB[(MySQL)]

    B1 --> C1
    B2 --> C2
    C1 --> C3
    C2 --> C3
    C3 --> DB
```

## Level 3 - Component (Application Core)
```mermaid
flowchart LR
    Auth[includes/auth.php]
    CSRF[includes/csrf.php]
    Helpers[includes/functions.php]
    DB[classes/Database.php]
    Admin[classes/Admin.php]
    BukuTamu[classes/BukuTamu.php]
    Kepuasan[classes/Kepuasan.php]

    Auth --> Admin
    Admin --> DB
    BukuTamu --> DB
    Kepuasan --> DB
    CSRF --> Helpers
```

## Level 4 - Code Hotspots
- `classes/Database.php`: singleton connection + query abstraction.
- `classes/BukuTamu.php`: insert pipeline, queue generation, filtering/stats/export.
- `classes/Kepuasan.php`: survey insert, rating stats, trend/export.
- `includes/auth.php`: session login/logout guard.
- `includes/csrf.php`: token generation/validation.

<!DOCTYPE html>
<html lang="ro">
<head>
  <meta charset="UTF-8">
  <title>HiveLap - Monitoring Sistem</title>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/p5.js/1.6.0/p5.min.js"></script>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Orbitron:wght@500&display=swap" rel="stylesheet">
  <style>
    :root {
      --bg: #0A0A0A;
      --panel: #151515;
      --accent: #00BFFF;
      --text: #E0E0E0;
      --border: #333;
      --highlight: #00F0FF;
      --success: #10B981;
      --warning: #F59E0B;
      --error: #EF4444;
    }

    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: 'Inter', sans-serif;
      background-color: var(--bg);
      color: var(--text);
      display: flex;
      height: 100vh;
      overflow: hidden;
    }

    .sidebar {
      background: linear-gradient(180deg, var(--panel) 0%, #0F0F0F 100%);
      width: 260px;
      padding: 2rem 1rem;
      display: flex;
      flex-direction: column;
      align-items: center;
      border-right: 1px solid var(--border);
    }

    .sidebar .brand {
      font-family: 'Orbitron', sans-serif;
      font-size: 1.8rem;
      color: var(--accent);
      margin-bottom: 2rem;
    }

    .sidebar nav ul {
      list-style: none;
      width: 100%;
    }

    .sidebar nav ul li {
      margin-bottom: 1.5rem;
    }

    .sidebar nav ul li a {
      display: flex;
      align-items: center;
      gap: 0.75rem;
      text-decoration: none;
      color: var(--text);
      padding: 0.75rem 1rem;
      border-radius: 0.5rem;
      transition: all 0.3s;
      font-weight: 500;
    }

    .sidebar nav ul li a i {
      font-size: 1.2rem;
      width: 20px;
      text-align: center;
    }

    .sidebar nav ul li a:hover {
      background-color: var(--accent);
      color: #fff;
    }

    .main-content {
      flex: 1;
      display: flex;
      flex-direction: column;
      overflow-y: auto;
    }

    .header {
      padding: 1.5rem 2rem;
      background-color: var(--panel);
      border-bottom: 1px solid var(--border);
      display: flex;
      align-items: center;
      justify-content: space-between;
    }

    .header h1 {
      font-family: 'Orbitron', sans-serif;
      font-size: 1.8rem;
      color: var(--accent);
    }

    .content {
      padding: 2rem;
      display: grid;
      grid-template-columns: 1fr 300px;
      gap: 2rem;
      flex: 1;
    }

    .monitoring-panel {
      background-color: var(--panel);
      border-radius: 1rem;
      padding: 1.5rem;
      border: 1px solid var(--border);
      display: flex;
      flex-direction: column;
      gap: 1.5rem;
    }

    .monitoring-panel h2 {
      font-size: 1.4rem;
      font-weight: 600;
      margin-bottom: 1rem;
      color: var(--accent);
    }

    .stats-grid {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 1rem;
    }

    .stat-card {
      background-color: var(--bg);
      border-radius: 0.5rem;
      padding: 1rem;
      border: 1px solid var(--border);
    }

    .stat-card h3 {
      font-size: 0.9rem;
      color: #9CA3AF;
      margin-bottom: 0.5rem;
    }

    .stat-card p {
      font-size: 1.5rem;
      font-weight: 600;
      color: var(--text);
    }

    .control-panel {
      background-color: var(--panel);
      border-radius: 1rem;
      padding: 1.5rem;
      border: 1px solid var(--border);
      display: flex;
      flex-direction: column;
      gap: 1rem;
    }

    .control-panel h2 {
      font-size: 1.25rem;
      font-weight: 600;
      margin-bottom: 1rem;
      color: var(--accent);
    }

    .control-panel button {
      width: 100%;
      padding: 1rem 1.5rem;
      background-color: var(--accent);
      color: #fff;
      border: none;
      border-radius: 0.5rem;
      font-weight: 500;
      cursor: pointer;
      transition: all 0.3s;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 0.75rem;
    }

    .control-panel button:hover {
      background-color: #1D4ED8;
      transform: translateY(-2px);
    }

    .control-panel button i {
      font-size: 1.2rem;
    }

    #canvasContainer {
      background-color: var(--panel);
      border-radius: 1rem;
      border: 1px solid var(--border);
      overflow: hidden;
    }

    canvas {
      display: block;
    }

    .status-indicator {
      display: flex;
      align-items: center;
      gap: 0.5rem;
      font-size: 0.875rem;
    }

    .status-dot {
      width: 8px;
      height: 8px;
      border-radius: 50%;
    }

    .status-active {
      background-color: var(--success);
    }

    .status-warning {
      background-color: var(--warning);
    }

    .status-error {
      background-color: var(--error);
    }
  </style>
</head>
<body>
  <div class="sidebar">
    <div class="brand">HiveLap</div>
    <nav>
      <ul>
        <li><a href="index.php"><i class="fa-solid fa-house"></i> Dashboard</a></li>
        <li><a href="#"><i class="fa-solid fa-chart-line"></i> Analytics</a></li>
        <li><a href="#"><i class="fa-solid fa-robot"></i> Devices</a></li>
        <li><a href="#"><i class="fa-solid fa-file-lines"></i> Reports</a></li>
        <li><a href="#"><i class="fa-solid fa-gear"></i> Settings</a></li>
        <li><a href="php/logout.php"><i class="fa-solid fa-right-from-bracket"></i> Logout</a></li>
      </ul>
    </nav>
  </div>

  <div class="main-content">
    <header class="header">
      <h1>Warehouse Robotics Monitoring System</h1>
    </header>
    <div class="content">
      <div class="monitoring-panel">
        <h2>Warehouse Overview</h2>
        <div class="stats-grid">
          <div class="stat-card">
            <h3>Active Robots</h3>
            <p id="activeRobots">0</p>
          </div>
          <div class="stat-card">
            <h3>Packages in Queue</h3>
            <p id="packagesInQueue">0</p>
          </div>
          <div class="stat-card">
            <h3>Completed Deliveries</h3>
            <p id="completedDeliveries">0</p>
          </div>
          <div class="stat-card">
            <h3>System Status</h3>
            <div class="status-indicator">
              <span class="status-dot status-active"></span>
              <span>Operational</span>
            </div>
          </div>
        </div>
        <div id="canvasContainer"></div>
      </div>

      <div class="control-panel">
        <h2>Control Panel</h2>
        <button onclick="orderPackage()"><i class="fa-solid fa-box"></i> Order Package</button>
        <button onclick="addRobot()"><i class="fa-solid fa-robot"></i> Add Robot</button>
        <div class="stat-card" style="margin-top: 1rem;">
          <h3>Current Tasks</h3>
          <div id="currentTasks"></div>
        </div>
      </div>
    </div>
  </div>

  <script>
    let robotImg, coletImg;
    function preload() {
      robotImg = loadImage("assets/robot.jpg");
      coletImg = loadImage("assets/colet.jpg");
    }

    class Node {
      constructor(x, y, g = 0, h = 0, parent = null) {
        this.x = x;
        this.y = y;
        this.g = g;  // Costul de la start la acest nod
        this.h = h;  // Estimarea costului de la acest nod la destinație
        this.f = g + h;  // Costul total estimat
        this.parent = parent;  // Nodul părinte pentru reconstruirea path-ului
      }
    }

    const cellSize = 60;
    const cols = 12, rows = 8;
    let robots = [], packages = [], destinations = [], gates = [], shelves = [], grid;
    let hub;
    let shelfStatus = {}; // Initialize shelfStatus as an empty object

    function setup() {
      const canvas = createCanvas(cols * cellSize, rows * cellSize);
      canvas.parent('canvasContainer');
      frameRate(30);

      gate = { x: 0, y: Math.floor(rows/2) };
      hub = { x: cols-1, y: rows-1 };

      setupShelves();
      updateGridWithShelves();
      addRobot();
      addRobot();
    }

    function setupShelves() {
      for (let row of [2, 5]) {
        for (let col of [3, 5, 7]) {
          shelves.push({ x: col, y: row });
          // Initialize shelf status as free
          shelfStatus[`${col},${row}`] = false;
        }
      }
    }

    function updateGridWithShelves() {
      grid = Array(rows).fill().map(() => Array(cols).fill(0));
      for (let shelf of shelves) {
        grid[shelf.y][shelf.x] = 1;
      }
    }

    function getRandomShelf() {
      return shelves[Math.floor(Math.random() * shelves.length)];
    }

    function addRobot() {
      const colors = ['#FF5252', '#4CAF50', '#2196F3', '#9C27B0', '#FF9800'];
      robots.push({
        id: robots.length,
        x: hub.x + 0.5,
        y: hub.y + 0.5,
        color: colors[robots.length % colors.length],
        hasPackage: false,
        state: 'idle',
        target: null,
        path: []
      });
    }

    function heuristic(a, b) {
      return Math.abs(a.x - b.x) + Math.abs(a.y - b.y);
    }

    function findPath(startX, startY, endX, endY, otherRobots) {
      const openList = [];  // Noduri de explorat
      const closedList = new Set();  // Noduri deja explorate
      const startNode = new Node(Math.floor(startX), Math.floor(startY));
      const goalNode = new Node(Math.floor(endX), Math.floor(endY));

      // Verifică dacă robotul pleacă din hub sau merge către hub
      const isLeavingHub = Math.floor(startX) === hub.x && Math.floor(startY) === hub.y;
      const isGoingToHub = Math.floor(endX) === hub.x && Math.floor(endY) === hub.y;

      // Filtrează roboții din hub din lista de obstacole dacă mergem către hub
      const filteredRobots = isGoingToHub ? 
        otherRobots.filter(r => !(Math.floor(r.x) === hub.x && Math.floor(r.y) === hub.y)) :
        otherRobots;

      openList.push(startNode);

      while (openList.length > 0) {
        let current = openList.reduce((min, node) => (node.f < min.f ? node : min));
        openList.splice(openList.indexOf(current), 1);
        closedList.add(`${current.x},${current.y}`);

        if (current.x === goalNode.x && current.y === goalNode.y) {
          const path = [];
          let temp = current;
          while (temp) {
            path.push({ x: temp.x + 0.5, y: temp.y + 0.5 });
            temp = temp.parent;
          }
          const fullPath = path.reverse();
          if (fullPath.length > 0 &&
              Math.abs(fullPath[0].x - startX) < 0.01 &&
              Math.abs(fullPath[0].y - startY) < 0.01) {
            return fullPath.slice(1);
          }
          return fullPath;
        }

        const directions = [
          { dx: 0, dy: -1 },  // Sus
          { dx: 1, dy: 0 },   // Dreapta
          { dx: 0, dy: 1 },   // Jos
          { dx: -1, dy: 0 }   // Stânga
        ];

        for (let dir of directions) {
          const newX = current.x + dir.dx;
          const newY = current.y + dir.dy;

          if (newX < 0 || newX >= cols || newY < 0 || newY >= rows ||
              closedList.has(`${newX},${newY}`)) {
            continue;
          }

          const isDestination = newX === Math.floor(endX) && newY === Math.floor(endY);
          if (grid[newY][newX] === 1 && !isDestination) {
            continue;
          }

          // Verifică dacă celula este hub
          const isHubCell = newX === hub.x && newY === hub.y;
          
          // Verifică coliziunea, dar ignoră complet dacă celula este hub
          const willCollide = filteredRobots.some(r =>
            Math.floor(r.x) === newX && Math.floor(r.y) === newY
          );

          if (!willCollide || isDestination || isHubCell) {
            const g = current.g + 1;
            const h = heuristic({ x: newX, y: newY }, goalNode);
            const newNode = new Node(newX, newY, g, h, current);

            const existing = openList.find(n => n.x === newX && n.y === newY);
            if (!existing || existing.f > newNode.f) {
              if (existing) openList.splice(openList.indexOf(existing), 1);
              openList.push(newNode);
            }
          }
        }
      }
      return [];
    }

    function hivemindMove() {
      const robotSpeed = 0.1; // viteza constantă (unități pe cadru)
      for (let robot of robots) {
        if (robot.path.length > 0) {
          const nextPos = robot.path[0];
          const otherRobots = robots.filter(r => r !== robot);

          // Verificăm dacă poziția următoare e ocupată (excepție pentru hub)
          const isPositionOccupied = otherRobots.some(r =>
            Math.abs(r.x - nextPos.x) < 0.5 &&
            Math.abs(r.y - nextPos.y) < 0.5
          );
          const isNextPosHub = Math.floor(nextPos.x) === hub.x && Math.floor(nextPos.y) === hub.y;

          if (!isPositionOccupied || isNextPosHub) {
            // Calculăm vectorul de deplasare spre nextPos
            const dx = nextPos.x - robot.x;
            const dy = nextPos.y - robot.y;
            const distance = sqrt(dx * dx + dy * dy);

            if (distance > robotSpeed) {
              // Normalizăm vectorul și deplasăm cu viteza constantă
              robot.x += (dx / distance) * robotSpeed;
              robot.y += (dy / distance) * robotSpeed;
            } else {
              // Dacă suntem aproape de nextPos, "snap-uim" la poziție
              robot.x = nextPos.x;
              robot.y = nextPos.y;
              robot.path.shift();
              if (robot.path.length === 0) {
                handleRobotArrival(robot);
              }
            }
          } else {
            // Replanificare pentru cazul în care poziția este ocupată
            let newPath;
            if (robot.state === 'toDestination' && robot.hasPackage) {
              newPath = findPath(robot.x, robot.y, robot.target.x, robot.target.y, otherRobots);
            } else if (robot.state === 'toHub') {
              newPath = findPath(robot.x, robot.y, hub.x, hub.y, otherRobots);
            }
            if (newPath && newPath.length > 0) {
              robot.path = newPath;
            }
          }
        } else if (robot.state === 'idle' && packages.length > 0) {
          assignPackageToRobot(robot, packages[0]);
          packages.shift();
        }
      }
    }

    function getLeastBusyShelf() {
      // Inițializează un obiect pentru a număra livrările pentru fiecare raft
      const counts = {};
      shelves.forEach(shelf => {
        const key = shelf.x + ',' + shelf.y;
        counts[key] = 0;
      });
      
      // Numără câte destinații (livrări) sunt deja alocate fiecărui raft
      destinations.forEach(dest => {
        const key = dest.x + ',' + dest.y;
        if (counts[key] !== undefined) {
          counts[key]++;
        }
      });
      
      // Găsește rafturile cu numărul minim de livrări
      let minCount = Infinity;
      let candidateShelves = [];
      shelves.forEach(shelf => {
        const key = shelf.x + ',' + shelf.y;
        if (counts[key] < minCount) {
          minCount = counts[key];
          candidateShelves = [shelf];
        } else if (counts[key] === minCount) {
          candidateShelves.push(shelf);
        }
      });
      
      // Alege aleatoriu un raft din cele candidate
      return candidateShelves[Math.floor(Math.random() * candidateShelves.length)];
    }

    function orderPackage() {
      // Folosește funcția getLeastBusyShelf() pentru a alege un raft echitabil
      const shelf = getLeastBusyShelf();
      const dest = {
        x: shelf.x,
        y: shelf.y
      };
      destinations.push(dest);
      
      const pkg = {
        x: gate.x + 0.5,
        y: gate.y + 0.5,
        destination: dest
      };
      packages.push(pkg);
      
      const idleRobot = robots.find(r => r.state === 'idle');
      if (idleRobot) {
        assignPackageToRobot(idleRobot, pkg);
      }
    }

    function assignPackageToRobot(robot, pkg) {
      // Elimină coletul din lista de colete imediat ce este asignat
      packages = packages.filter(p => p !== pkg);
      robot.state = 'toPackage';
      robot.target = pkg;  // păstrează și proprietatea 'destination'
      const otherRobots = robots.filter(r => r !== robot);
      robot.path = findPath(
        robot.x, robot.y,
        pkg.x,
        pkg.y,
        otherRobots
      );
      // Verifică dacă s-a găsit un path valid
      if (robot.path.length === 0) {
        console.log(`Robot ${robot.id} nu a putut găsi un path către colet`);
        robot.state = 'idle';
        packages.push(pkg); // readaugă coletul în listă
      }
    }

    function handleRobotArrival(robot) {
      if (robot.state === 'toPackage') {
        robot.hasPackage = true;
        // Actualizează targetul: folosim doar destinația de livrare
        const deliveryTarget = robot.target.destination;
        robot.state = 'toDestination';
        robot.target = { 
          x: deliveryTarget.x, 
          y: deliveryTarget.y,
          destination: deliveryTarget
        };
        const otherRobots = robots.filter(r => r !== robot);
        
        robot.path = findPath(
          robot.x, robot.y,
          robot.target.x,
          robot.target.y,
          otherRobots
        );
        
        // Verifică dacă s-a găsit un path valid
        if (robot.path.length === 0) {
          console.log(`Robot ${robot.id} nu a putut găsi un path către raft`);
          // Încearcă să găsească un raft liber
          const freeShelf = shelves.find(shelf => !shelfStatus[`${shelf.x},${shelf.y}`]);
          if (freeShelf) {
            robot.target = { 
              x: freeShelf.x, 
              y: freeShelf.y,
              destination: freeShelf
            };
            robot.path = findPath(
              robot.x, robot.y,
              freeShelf.x,
              freeShelf.y,
              otherRobots
            );
          } else {
            // Dacă nu există rafturi libere, returnează la hub
            robot.state = 'toHub';
            robot.path = findPath(
              robot.x, robot.y,
              hub.x,
              hub.y,
              otherRobots
            );
          }
        }
      }
      else if (robot.state === 'toDestination') {
        const shelfKey = `${robot.target.x},${robot.target.y}`;
        if (!shelfStatus[shelfKey]) {
          shelfStatus[shelfKey] = true;  // marchează shelf-ul ca ocupat

          robot.hasPackage = false;
          destinations = destinations.filter(d =>
            !(d.x === robot.target.x && d.y === robot.target.y)
          );
          
          if (packages.length > 0) {
            assignPackageToRobot(robot, packages[0]);
            packages.shift();
          } else {
            robot.state = 'toHub';
            const otherRobots = robots.filter(r => r !== robot);
            robot.path = findPath(
              robot.x, robot.y,
              hub.x,
              hub.y,
              otherRobots
            );
          }

          // eliberează shelf-ul după scurt timp (simulează descărcarea)
          setTimeout(() => {
            shelfStatus[shelfKey] = false;
          }, 1000);

        } else {
          // dacă shelf-ul e ocupat, replanifică drumul dar nu abandonează coletul
          const otherRobots = robots.filter(r => r !== robot);
          robot.path = findPath(
            robot.x, robot.y,
            robot.target.x,
            robot.target.y,
            otherRobots
          );
          
          // Dacă nu se poate găsi un path, încearcă să găsească un raft liber
          if (robot.path.length === 0) {
            const freeShelf = shelves.find(shelf => !shelfStatus[`${shelf.x},${shelf.y}`]);
            if (freeShelf) {
              robot.target = { 
                x: freeShelf.x, 
                y: freeShelf.y,
                destination: freeShelf
              };
              robot.path = findPath(
                robot.x, robot.y,
                freeShelf.x,
                freeShelf.y,
                otherRobots
              );
            }
          }
        }
      }
      else if (robot.state === 'toHub') {
        robot.state = 'idle';
        if (packages.length > 0) {
          assignPackageToRobot(robot, packages[0]);
          packages.shift();
        }
      }
    }

    function draw() {
      background('#1a1a2e');
      drawGrid();
      
      fill('#FFD700');
      rect(gate.x * cellSize, gate.y * cellSize, cellSize, cellSize);
      fill('#4CAF50');
      rect(hub.x * cellSize, hub.y * cellSize, cellSize, cellSize);
      
      for (let shelf of shelves) {
        fill('#8B4513');
        rect(shelf.x * cellSize, shelf.y * cellSize, cellSize, cellSize);
        fill('#A0522D');
        rect(shelf.x * cellSize + 10, shelf.y * cellSize + 5, 
             cellSize - 20, cellSize - 10);
      }
      
      for (let dest of destinations) {
        fill('rgba(33, 150, 243, 0.3)');
        rect(dest.x * cellSize, dest.y * cellSize, cellSize, cellSize);
      }
      
      for (let pkg of packages) {
        fill('#FF5252');
        rect(pkg.x * cellSize - 15, pkg.y * cellSize - 15, 30, 30);
      }
      
      hivemindMove();
      
      // Desenează roboții cu efect de puls
      for (let i = 0; i < robots.length; i++) {
        const robot = robots[i];
        const posX = robot.x * cellSize;
        const posY = robot.y * cellSize;
        
        // Calculează un factor de puls (doar pentru roboții activi)
        let pulse = 1;
        if (robot.state !== 'idle') {
          // Folosește robot.id ca offset pentru a nu avea toți roboții în același ritm
          pulse = map(sin(frameCount * 0.1 + robot.id), -1, 1, 0.9, 1.1);
        }
        
        // Desenează robotul cu efect de puls
        fill(robot.color);
        circle(posX, posY, 40 * pulse);
        
        // Desenăm indicativ coletul dacă robotul are unul
        if (robot.hasPackage) {
          fill('#FF5252');
          rect(posX - 10, posY - 25, 20, 20);
        }
        
        // Desenăm traseul robotului
        if (robot.path.length > 0) {
          stroke(robot.color);
          strokeWeight(2);
          noFill();
          beginShape();
          vertex(posX, posY);
          for (let point of robot.path) {
            vertex(point.x * cellSize, point.y * cellSize);
          }
          endShape();
        }
      }
      
      updateStats();
    }

    function drawGrid() {
      stroke('#333');
      strokeWeight(1);
      for (let i = 0; i <= cols; i++) {
        line(i * cellSize, 0, i * cellSize, height);
      }
      for (let j = 0; j <= rows; j++) {
        line(0, j * cellSize, width, j * cellSize);
      }
    }

    function updateStats() {
      document.getElementById('activeRobots').textContent = robots.length;
      document.getElementById('packagesInQueue').textContent = packages.length;
      
      const tasksList = document.getElementById('currentTasks');
      tasksList.innerHTML = '';
      
      robots.forEach(robot => {
        const taskDiv = document.createElement('div');
        taskDiv.style.marginTop = '0.5rem';
        taskDiv.style.fontSize = '0.875rem';
        
        let status = '';
        if (robot.state === 'idle') {
          status = 'Idle';
        } else if (robot.state === 'toPackage') {
          status = 'Collecting Package';
        } else if (robot.state === 'toDestination') {
          status = 'Delivering Package';
        } else if (robot.state === 'toHub') {
          status = 'Returning to Hub';
        }
        
        taskDiv.textContent = `Robot ${robot.id}: ${status}`;
        tasksList.appendChild(taskDiv);
      });
    }
  </script>
</body>
</html>



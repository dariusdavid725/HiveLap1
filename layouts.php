<?php
ob_start();
session_start();
if (!isset($_SESSION['user_id'])) {
  header("Location: php/register.php");
  exit;
}
?>
<!DOCTYPE html>
<html lang="ro">
<head>
  <meta charset="UTF-8">
  <title>Warehouse Layout Editor</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <script src="https://cdnjs.cloudflare.com/ajax/libs/interact.js/1.10.11/interact.min.js"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    :root {
      --main-bg: #1c1f26;
      --primary: #4CAF50;
      --accent: #2e7d32;
      --text: #ffffff;
      --grid-bg: #2a2e38;
      --cell-border: #444;
    }
    body, html {
      margin: 0;
      padding: 0;
      background: var(--main-bg);
      font-family: 'Segoe UI', sans-serif;
      color: var(--text);
    }
    header {
      background: #111;
      padding: 1rem 2rem;
      display: flex;
      justify-content: space-between;
      align-items: center;
      border-bottom: 1px solid #333;
    }
    .logo {
      font-size: 1.5rem;
      font-weight: bold;
      display: flex;
      align-items: center;
      gap: 10px;
    }
    nav ul {
      list-style: none;
      display: flex;
      gap: 20px;
    }
    nav a {
      color: var(--text);
      text-decoration: none;
      padding: 8px 16px;
      border-radius: 4px;
      transition: background 0.3s;
    }
    nav a:hover {
      background: var(--primary);
    }
    .container {
      max-width: 1200px;
      margin: 2rem auto;
      padding: 1rem;
    }
    .form-section {
      background: #222;
      padding: 20px;
      border-radius: 10px;
      margin-bottom: 20px;
      box-shadow: 0 4px 10px rgba(0,0,0,0.5);
    }
    .form-section h2 {
      margin-bottom: 1rem;
    }
    .form-section label {
      display: block;
      margin-top: 10px;
    }
    .form-section input {
      width: 100%;
      padding: 10px;
      margin-top: 5px;
      border-radius: 5px;
      border: none;
      background: #333;
      color: white;
    }
    .form-section button {
      margin-top: 1rem;
      padding: 10px 20px;
      background: var(--primary);
      border: none;
      color: white;
      border-radius: 5px;
      cursor: pointer;
      transition: background 0.3s;
    }
    .form-section button:hover {
      background: var(--accent);
    }
    .toolbar {
      display: flex;
      gap: 20px;
      flex-wrap: wrap;
      margin-bottom: 1rem;
    }
    .draggable-item {
      width: 100px;
      height: 100px;
      background: #333;
      display: flex;
      align-items: center;
      justify-content: center;
      flex-direction: column;
      border-radius: 8px;
      cursor: grab;
      border: 2px solid var(--primary);
    }
    .draggable-item span {
      margin-top: 5px;
      font-size: 0.9rem;
    }
    #warehouse {
      position: relative;
      background: var(--grid-bg);
      box-shadow: inset 0 0 10px rgba(0,0,0,0.5);
      margin-top: 2rem;
      border: 2px solid var(--primary);
    }
    .grid-cell {
      position: absolute;
      box-sizing: border-box;
      border: 1px solid var(--cell-border);
    }
    .element {
      position: absolute;
      box-sizing: border-box;
      border-radius: 4px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 0.8rem;
      font-weight: bold;
      text-shadow: 0 0 2px black;
      color: white;
      user-select: none;
      z-index: 10;
    }
    .shelf { background: #8B4513; }
    .gate { background: #FFD700; color: #000; }
    .obstacle { background: #555; }
    .hub { background: #4CAF50; }
  </style>
</head>
<body>
  <header>
    <div class="logo"><i class="fa-solid fa-warehouse"></i> Warehouse Hub</div>
    <nav>
      <ul>
        <li><a href="../index.php"><i class="fa-solid fa-house"></i> Acasă</a></li>
        <li><a href="../php/logout.php"><i class="fa-solid fa-sign-out-alt"></i> Logout</a></li>
      </ul>
    </nav>
  </header>

  <div class="container">
    <div class="form-section">
      <h2>Configurare depozit</h2>
      <form id="layoutForm">
        <label for="warehouseWidth">Lățime (m):</label>
        <input type="number" id="warehouseWidth" value="20" required>
        <label for="warehouseHeight">Înălțime (m):</label>
        <input type="number" id="warehouseHeight" value="15" required>
        <label for="layoutName">Nume layout:</label>
        <input type="text" id="layoutName" required>
        <label for="numRoboti">Număr roboți:</label>
        <input type="number" id="numRoboti" value="3" min="1" max="15" required>
        <button type="submit">Aplică</button>
      </form>
    </div>

    <div class="toolbar">
      <div class="draggable-item" draggable="true" data-type="shelf">
        <i class="fa-solid fa-box"></i><span>Raft</span>
      </div>
      <div class="draggable-item" draggable="true" data-type="gate">
        <i class="fa-solid fa-door-open"></i><span>Gate</span>
      </div>
      <div class="draggable-item" draggable="true" data-type="obstacle">
        <i class="fa-solid fa-ban"></i><span>Obstacol</span>
      </div>
      <div class="draggable-item" draggable="true" data-type="hub">
        <i class="fa-solid fa-robot"></i><span>Dock</span>
      </div>
      <button id="saveLayoutBtn">Salvează Layout</button>
    </div>

    <div id="warehouse"></div>
  </div>

  <script>
    const cellSize = 50;
    const warehouse = document.getElementById("warehouse");
    const layoutForm = document.getElementById("layoutForm");
    let gridCols = 0, gridRows = 0;
    let placedElements = [];

    layoutForm.addEventListener("submit", e => {
      e.preventDefault();
      const width = parseInt(document.getElementById("warehouseWidth").value);
      const height = parseInt(document.getElementById("warehouseHeight").value);
      gridCols = width;
      gridRows = height;

      warehouse.innerHTML = "";
      warehouse.style.width = (cellSize * width) + "px";
      warehouse.style.height = (cellSize * height) + "px";

      for (let y = 0; y < height; y++) {
        for (let x = 0; x < width; x++) {
          const cell = document.createElement("div");
          cell.classList.add("grid-cell");
          cell.style.left = `${x * cellSize}px`;
          cell.style.top = `${y * cellSize}px`;
          cell.style.width = `${cellSize}px`;
          cell.style.height = `${cellSize}px`;
          warehouse.appendChild(cell);
        }
      }

      placedElements = [];
    });

    document.querySelectorAll(".draggable-item").forEach(item => {
      item.addEventListener("dragstart", e => {
        e.dataTransfer.setData("type", item.getAttribute("data-type"));
      });
    });

    warehouse.addEventListener("dragover", e => e.preventDefault());

    warehouse.addEventListener("drop", e => {
      e.preventDefault();
      const type = e.dataTransfer.getData("type");
      const rect = warehouse.getBoundingClientRect();
      let x = Math.floor((e.clientX - rect.left) / cellSize);
      let y = Math.floor((e.clientY - rect.top) / cellSize);

      const el = document.createElement("div");
      el.classList.add("element", type);
      el.style.width = type === "hub" ? `${cellSize * 2}px` : `${cellSize}px`;
      el.style.height = type === "hub" ? `${cellSize * 2}px` : `${cellSize}px`;
      el.style.left = `${x * cellSize}px`;
      el.style.top = `${y * cellSize}px`;
      el.innerText = type === "hub" ? "Dock" : type.charAt(0).toUpperCase() + type.slice(1);
      warehouse.appendChild(el);

      el.addEventListener("dblclick", () => {
        el.remove();
        placedElements = placedElements.filter(p => p.element !== el);
      });

      interact(el).draggable({
        modifiers: [interact.modifiers.restrictRect({ restriction: warehouse, endOnly: true })],
        listeners: {
          move (event) {
            const target = event.target;
            const x = (parseFloat(target.getAttribute('data-x')) || 0) + event.dx;
            const y = (parseFloat(target.getAttribute('data-y')) || 0) + event.dy;
            target.style.transform = `translate(${x}px, ${y}px)`;
            target.setAttribute('data-x', x);
            target.setAttribute('data-y', y);
          },
          end (event) {
            const target = event.target;
            let dx = parseFloat(target.getAttribute('data-x')) || 0;
            let dy = parseFloat(target.getAttribute('data-y')) || 0;
            let left = parseInt(target.style.left) + dx;
            let top = parseInt(target.style.top) + dy;
            let snapX = Math.round(left / cellSize);
            let snapY = Math.round(top / cellSize);
            target.style.left = (snapX * cellSize) + "px";
            target.style.top = (snapY * cellSize) + "px";
            target.style.transform = "";
            target.setAttribute("data-x", 0);
            target.setAttribute("data-y", 0);

            const existing = placedElements.find(p => p.element === target);
            if (existing) {
              existing.col = snapX;
              existing.row = snapY;
            } else {
              placedElements.push({ col: snapX, row: snapY, type: target.classList[1], w: type === "hub" ? 2 : 1, h: type === "hub" ? 2 : 1, element: target });
            }
          }
        }
      });

      placedElements.push({ col: x, row: y, type: type, w: type === "hub" ? 2 : 1, h: type === "hub" ? 2 : 1, element: el });
    });

    document.getElementById("saveLayoutBtn").addEventListener("click", () => {
      const layoutName = document.getElementById("layoutName").value.trim();
      const layout = {
        layoutName,
        warehouseWidth: gridCols,
        warehouseHeight: gridRows,
        cols: gridCols,
        rows: gridRows,
        roboti: parseInt(document.getElementById("numRoboti").value),
        elements: placedElements.map(p => ({ col: p.col, row: p.row, type: p.type, w: p.w, h: p.h }))
      };

      fetch("../php/save_layout.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(layout)
      })
      .then(res => res.text())
      .then(data => alert(data))
      .catch(err => alert("Eroare: " + err));
    });
  </script>
</body>
</html>

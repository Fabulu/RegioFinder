# Jurapark Verkäufer-Portal – Frontend (Windows + PowerShell)

Voraussetzung: Internetzugang. Kein Backend nötig.

INSTALL NODE.JS (einmalig)
- Öffne https://nodejs.org
- Lade "LTS" herunter und installiere (Default-Optionen, "Add to PATH" aktiv lassen)
- PowerShell schließen und neu öffnen
- Prüfen:
  node -v
  npm -v

PROJEKT STARTEN
- In dein Repo wechseln:
  cd C:\pfad\zu\deinem\repo
- In den Frontend-Ordner (wo package.json liegt):
  cd frontend
- Dependencies installieren (einmalig pro frischem Checkout):
  npm install
- Dev-Server starten:
  npm run dev
- Browser öffnen:
  http://localhost:5173

STOPPEN
- Im PowerShell-Fenster:
  CTRL + C

TROUBLESHOOTING
- "node" nicht gefunden:
  PowerShell neu öffnen; falls nötig Node LTS neu installieren
- Port belegt:
  npm run dev -- --port 5174
  dann öffnen: http://localhost:5174

KURZVERSION
cd frontend
npm install
npm run dev

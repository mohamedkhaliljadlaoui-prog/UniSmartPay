# UniSmartPay — Livrables (LaTeX)

Ce repo contient :
- le **rapport final** (40 pages max, 4–5 chapitres) : `report/main.tex`
- les **slides** (≤ 15) : `slides/slides.tex`
- le **document technique (Rendu final)** : `report/rapport_technique.tex`

## 1) Compiler le rapport final (PDF)

### Option A — latexmk (recommandé)
PowerShell (Windows) :
```powershell
Set-Location report
latexmk -pdf main.tex
```

### Option B — pdflatex
```powershell
Set-Location report
pdflatex .\main.tex
pdflatex .\main.tex
```

Sortie attendue : `report/main.pdf`

## 2) Compiler les slides (PDF)

### Option A — latexmk
```powershell
Set-Location slides
latexmk -pdf slides.tex
```

### Option B — pdflatex
```powershell
Set-Location slides
pdflatex .\slides.tex
pdflatex .\slides.tex
```

Sortie attendue : `slides/slides.pdf`

## 3) Compiler le document technique (Rendu final)

```powershell
Set-Location report
pdflatex .\rapport_technique.tex
pdflatex .\rapport_technique.tex
```

Sortie attendue : `report/rapport_technique.pdf`

## 4) Images (captures d’écran)

Déposez vos images (PNG/JPG) dans : `report/figures/`.

Le rapport et les slides sont **résilients** : s’il manque une image, un cadre placeholder s’affiche (le PDF compile quand même).

### Noms attendus (déjà référencés)
- `report/figures/ui_admin_login.png`
- `report/figures/ui_student_login.png`
- `report/figures/ui_admin_dashboard.png`
- `report/figures/ui_student_dashboard.png` *(optionnel, utilisé dans les slides)*
- `report/figures/ui_terminal_resto.png` *(optionnel)*
- `report/figures/ui_terminal_buvette.png` *(optionnel)*
- `report/figures/ui_payment_resto.png` *(optionnel)*
- `report/figures/ui_serial_monitor.png` *(optionnel)*
- `report/figures/ui_student_card.png` *(optionnel)*
- `report/figures/architecture.png` *(optionnel : schéma d’architecture)*
- `report/figures/rfid_step1_read_uid.png`
- `report/figures/rfid_step2_api_check.png`
- `report/figures/rfid_step3_led_feedback.png`

## 5) Champs à compléter

Dans `report/main.tex`, `slides/slides.tex` et `report/rapport_technique.tex`, compléter :
- noms des membres
- classe
- lien GitHub

## 6) Conformité consignes (Classroom)

- Rapport final : PDF uniquement — **40 pages max** — **4 ou 5 chapitres** — nom : `Nom1_Nom2_Nom3_Nom4_Nom5_RAPPORT.pdf`
- Slides : PDF uniquement — **15 slides max** — nom : `Nom1_Nom2_Nom3_Nom4_Nom5_SLIDES.pdf`
- Document technique (Rendu final) : PDF uniquement — doit contenir infos groupe + lien GitHub + exécution + doc technique courte + guide utilisateur — nom : `Nom1_Nom2_Nom3_Nom4_Nom5_RenduFinal.pdf`

Important (document technique) : ne mentionnez pas la vidéo dans ce PDF. La vidéo est déposée séparément dans le devoir dédié.

## 7) Renommer les PDF avant dépôt

Après compilation, vous devez renommer les fichiers selon le format exigé.

Exemple PowerShell (depuis la racine du repo) :
```powershell
Copy-Item .\report\main.pdf .\Nom1_Nom2_Nom3_Nom4_Nom5_RAPPORT.pdf
Copy-Item .\slides\slides.pdf .\Nom1_Nom2_Nom3_Nom4_Nom5_SLIDES.pdf
Copy-Item .\report\rapport_technique.pdf .\Nom1_Nom2_Nom3_Nom4_Nom5_RenduFinal.pdf
```

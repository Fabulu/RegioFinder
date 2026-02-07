class HofladenList extends HTMLElement {


    constructor() {

        super();


    }

    connectedCallback() {


        //const p = document.createElement("p");
        //p.textContent = "Hello Hofladen";

        // Alles in Shadow DOM anhängen
        //shadow.appendChild(style);
        //this.appendChild(p);


        this.loadData();

    }


    async loadData() {
        //this.innerText = "Lade...";

        //let domain = "http://localhost:9191";
        let domain = "https://regiomap.nemundo.ch";
        let url = domain + "/api.php";

        try {
            const res = await fetch(url);

            if (!res.ok) {
                throw new Error("HTTP " + res.status);
            }

            const data = await res.json();


            for (const item of data) {

                console.log(item);

                //   const {id, betrieb, betriebstyp, strasse, plz, ort, web, lat, lon} = item;

                const p = document.createElement("p");
                p.textContent = item.betrieb;  // "Hello Hofladen";

                // Alles in Shadow DOM anhängen
                //shadow.appendChild(style);
                this.appendChild(p);


                const div = document.createElement("div");
                div.textContent = item.strasse;
                this.appendChild(div);

                const divOrt = document.createElement("div");
                divOrt.textContent = item.plz + " " + item.ort;
                this.appendChild(divOrt);


                const div2 = document.createElement("div");

                let hyperlink = document.createElement("a");
                hyperlink.href = item.web;
                hyperlink.innerText = item.web;
                hyperlink.target = "_blank";

                this.appendChild(div2);
                div2.appendChild(hyperlink);

            }

        } catch (err) {
            this.innerText = "Fehler: " + err.message;
        }
    }

}

customElements.define('hofladen-list', HofladenList);




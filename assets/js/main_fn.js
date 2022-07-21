// FUNCIONES GENERALES

/**
 * Filtro de opciones para selecciondes segun el dato proporcionado
 * @param {string} selectId - El id del select a rellenar
 * @param {string} textoPorDefecto - El texto que recibira la opcion por defecto del select
 * @param {string} receptorPhp - La condicion en PHP que recibira y emitira los datos
 * @param {string} empresa - La empresa 
 * @param {string} depto - La condicion en PHP que recibira y emitira los datos
 */

function opciones_select(selectId, textoPorDefecto, receptorPhp, empresa = null, depto = null) {
    console.log("ReceptorPHP: " + receptorPhp)
    let emp = dep = '&';
    if (empresa) {
        emp = `&empresa=${empresa}`;
    }
    if (depto) {
        dep = `&depto=${depto}`;
    }

    fetch(`main_controller.php?${receptorPhp}=true${emp}${dep}`)
        .then(res => res.json())
        .then(data => {
            let nodo = document.getElementById(selectId);
            if (data.length > 0) {
                nodo.innerHTML = `<option style='color:#aaa' selected>${textoPorDefecto}</option>`;
                data.forEach(dep => {
                    let opt = document.createElement("option");
                    opt.setAttribute("value", dep);
                    opt.innerText = dep;
                    nodo.append(opt);
                })
            } else {
                nodo.innerHTML = "<option style='color:#aaa' selected>Sin registros</option>";
                console.warn("AVISO: sin registros activos.")
            }
        })
}

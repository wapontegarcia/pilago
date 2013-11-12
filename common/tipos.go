package common

import (
	"errors"
	"fmt"
	db "github.com/santios/pila/database"
	"log"
	"strconv"
	"strings"
)

var (
	ErrEncabezado            = errors.New("Error en el encabezado")
	ErrDetalle               = errors.New("Error en el detalle")
	ErrTotales               = errors.New("Error en los totales")
	ErrSoloUnDetalleEsperado = errors.New("Se esperaba solo un detalle")
)

type Archivos struct {
	Archivos []ArchivoPila
	Total    int
}

type ArchivoPila struct {
	Zip             string
	Nombre          string
	Lineas          [][]byte
	TotalLineas     int
	TotalDetalles   int
	TotalTotales    int
	Enc             *Encabezado
	Det             []*Detalle
	Tot31           *Totales31
	Tot36           *Totales36
	Tot39           *Totales39
	Devuelto        bool
	RezagosEnc      []int
	RezagosDetalles []RezagoDetalle
}

type RezagoDetalle struct {
	Linea  int
	Codigo int
}

type Encabezado struct {
	NroRegistro       int
	TipoRegistro      int
	CodFormato        int
	IdAfp             int
	Digito            int
	RazonSocial       string
	TipoId            string
	IdAportante       string
	DigitoAportante   string
	Direccion         string
	CodCiudad         string
	CodDep            string
	Telefono          string
	Fax               string
	Mail              string
	Periodo           string
	FechaPago         string
	Planilla          int
	FormaPresentacion string
	CodSucursal       string
	NomSucursal       string
	TotalEmpleados    int
	Afiliados         int
	Operador          int
	TipoAportante     string
	Arp               string
	TipoPlanilla      string
	FechaPagoAsociada string
	PlanillaAsociada  string
	DiasMora          int
	Modalidad         int
	RegistrosTipo2    int
}

type Detalle struct {
	NroRegistro                   int
	TipoRegistro                  int
	TipoDocumento                 string
	IdAfiliado                    string
	TipoCotizante                 int
	SubtipoCotizante              int
	Extanjero                     string
	ColExterior                   string
	CodDepartamento               string
	CodMunicipio                  string
	PrimerApellido                string
	SegundoApellido               string
	PrimerNombre                  string
	SegundoNombre                 string
	Ing                           string
	Ret                           string
	Tdp                           string
	Tap                           string
	Vsp                           string
	Vst                           string
	Sln                           string
	Ige                           string
	Lma                           string
	Vac                           string
	Avp                           string
	Dias                          int
	Salario                       int
	Ibc                           int
	Tarifa                        int
	CotizacionObligatoria         int
	CotizacionVoluntariaAf        int
	CotizacionVoluntariaAportante int
	TotalCot                      int
	Fsp                           int
	Afsp                          int
	ValorNoRetenido               int
	Correcciones                  string
	SalarioIntegral               string
	PuntoEstructura               string
}

type Totales31 struct {
	TotalAportes                  int
	TipoRegistro                  int
	Ibc                           int
	CotizacionObli                int
	CotizacionVoluntariaAf        int
	CotizacionVoluntariaAportante int
	TotalCot                      int
	Fsp                           int
	Fspsub                        int
}

type Totales36 struct {
	Intereses                     int
	TipoRegistro                  int
	Ibc                           int
	CotizacionObli                int
	CotizacionVoluntariaAf        int
	CotizacionVoluntariaAportante int
	TotalCot                      int
	Fsp                           int
	Fspsub                        int
	DiasMora                      int
	MoraCotizaciones              int
	MoraFsp                       int
	MoraFspsub                    int
}

type Totales39 struct {
	TotalAPagar                   int
	TipoRegistro                  int
	Ibc                           int
	CotizacionObli                int
	CotizacionVoluntariaAf        int
	CotizacionVoluntariaAportante int
	TotalCot                      int
	Fsp                           int
	Fspsub                        int
}

func NewArchivos() *Archivos {
	return &Archivos{}
}

func NewArchivo(nombre string, nombrezip string) *ArchivoPila {
	return &ArchivoPila{
		Zip:             nombrezip,
		Nombre:          nombre,
		TotalDetalles:   0,
		TotalTotales:    0,
		RezagosEnc:      []int{},
		RezagosDetalles: []RezagoDetalle{},
	}
}

func (a *Archivos) AppendArchivo(ap ArchivoPila) {
	a.Archivos = append(a.Archivos, ap)
}

func (a *ArchivoPila) AppendLinea(l []byte) {
	a.Lineas = append(a.Lineas, l)
}

func TipoIdEmpresa(tipo string) string {
	if tipo == "NI" {
		tipo = "NIT"
	}
	return tipo
}

func (a *ArchivoPila) MarcaRezago(tipo int) {
	a.RezagosEnc = append(a.RezagosEnc, tipo)
}

func (a *ArchivoPila) AgregarRezagoDetalle(tipo int, ubicacion int) {
	rez := RezagoDetalle{tipo, ubicacion}
	a.RezagosDetalles = append(a.RezagosDetalles, rez)
}

func (a *ArchivoPila) ValidarNitEncabezado() {
	nit := a.Enc.IdAfp
	if nit != 800229739 && nit != 800229739 {
		a.Devuelto = true
	}
}

func (a *ArchivoPila) ValidarPuntoEnTipoDos(punto string) {
	if punto != "." {
		a.Devuelto = true
	}
}

func EliminarLineasYEstructuras(a *ArchivoPila, indice int, borrados int) {
	indiceDef := 0
	if borrados > indice {
		indiceDef = borrados - indice
	} else {
		indiceDef = indice - borrados
	}
	//Aqui contemplamos la eliminación del último elemento
	if len(a.Det) == indiceDef {
		a.Det = append(a.Det[:indiceDef-1])
	} else {
		a.Det = append(a.Det[:indiceDef], a.Det[indiceDef+1:]...)
	}
	//En este caso, nunca eliminaremos el ultimo slice
	a.Lineas = append(a.Lineas[:indiceDef+1], a.Lineas[indiceDef+2:]...)
}

func RegistrosDuplicados(ds []*Detalle) map[string][]int {
	idx := 0
	empty := []int{}
	m := make(map[string][]int)
	for _, d := range ds {

		if _, present := m[d.IdAfiliado]; present {
			m[d.IdAfiliado] = append(m[d.IdAfiliado], idx)

		} else {
			m[d.IdAfiliado] = empty
			m[d.IdAfiliado] = append(m[d.IdAfiliado], idx)

		}
		idx += 1
	}

	for k, positions := range m {
		if len(positions) <= 1 {
			delete(m, k)
		}
	}

	return m
}

func (a *ArchivoPila) ClasificarLineas() {
	for _, lineaByte := range a.Lineas {
		linea := string(lineaByte)

		switch tipoLinea := linea[5:6]; tipoLinea {
		case "1":
			a.TipoUno(linea)
		case "2":
			a.TipoDos(linea)
			a.TotalDetalles += 1
		case "3":
			a.TipoTres(linea)
			a.TotalTotales += 1
		default:
			fmt.Println(linea)
			fmt.Println("--------****--------")

		}
	}

	a.ValidarEstructuraDeForma()
	a.ValidarFondo()
	//fmt.Printf("Total detalles: %d, Total totales: %d\n", a.TotalDetalles, a.TotalTotales)
}

func (a *ArchivoPila) ValidarEstructuraDeForma() {
	a.ValidarNitEncabezado()
	a.ValidarTresRegistrosDeTotales()
}

func (a *ArchivoPila) ValidarFondo() {
	a.ActualizarTipoAportante()
	a.ActualizarTipoPlanilla()
	a.ActualizarPresentacion()
	a.ArreglarTelefonoFaxDireccion()
	a.ActualizarCantidadRegistrosTipoDos()

	//a.ActualizarClaseAportante() @todo Validar con Jenry. No se está leyendo del archivo pila

	a.CambioAVoluntario()
	a.ValidarTipoPlanilla()
	a.ValidarDatosPlanilla() //Tipoid, presentacion, codigo ciudad y dep
	a.ValidarPeriodo()
	a.CruzarLogBancario() //@todo
	a.ValidarPlanillaTipoN()
	a.UnificarRegistros()
	a.UnificarRegistrosTipoN()
	a.ValidarDetalles()

}

func (a *ArchivoPila) ValidarDetalles() {
	eliminadas := []int{}
	for _, v := range a.Det {
		//@todo Validar con jenry, vte no está en pila
		if esRezago, codigo := v.MenoresA30SinNovedad(); esRezago {
			a.AgregarRezagoDetalle(codigo, v.NroRegistro)
		}
		//
		/*
			@todo Se quitaron porque Jenry dijo
			if esRezago, codigo := v.SalarioIbc(); esRezago {
				a.AgregarRezagoDetalle(codigo, v.NroRegistro)
			}
			if esRezago, codigo := v.ValidarCotizacion(); esRezago {
				a.AgregarRezagoDetalle(codigo, v.NroRegistro)
			}
		*/

		if esRezago, codigo := v.ValidarSubtipoCotizante(); esRezago {
			a.AgregarRezagoDetalle(codigo, v.NroRegistro)
		}

		if esRezago, codigo := v.ValidarValoresEnCero(); esRezago {
			a.AgregarRezagoDetalle(codigo, v.NroRegistro)
			eliminadas = append(eliminadas, v.NroRegistro)
		}
	}

	borrados := 0
	for _, v := range eliminadas {
		EliminarLineasYEstructuras(a, v, borrados)
		borrados += 1
	}

	a.Enc.RegistrosTipo2 = a.Enc.RegistrosTipo2 - borrados
	copy(a.Lineas[0][492:], []byte(fmt.Sprintf("%08d", a.Enc.RegistrosTipo2)))

	//@todo Mejorar para no recorrer de nuevo los detalles
	a.CompararTotales()

}

func (a *ArchivoPila) ArreglarTelefonoFaxDireccion() {
	tel := a.Enc.Telefono
	fax := a.Enc.Fax
	dir := a.Enc.Direccion
	if tel == "" {
		a.Enc.Telefono = "0"
		copy(a.Lineas[0][290:], []byte(fmt.Sprintf("% -10s", a.Enc.Telefono)))
	}

	if fax == "" {
		a.Enc.Fax = "0"
		copy(a.Lineas[0][300:], []byte(fmt.Sprintf("% -10s", a.Enc.Fax)))
	}

	if dir == "" {
		a.Enc.Direccion = "NO REPORTA"
		copy(a.Lineas[0][245:], []byte(fmt.Sprintf("% -40s", a.Enc.Direccion)))
	}

}

func (a *ArchivoPila) ActualizarCantidadRegistrosTipoDos() {

	if a.TotalDetalles != a.Enc.RegistrosTipo2 {
		a.Enc.RegistrosTipo2 = a.TotalDetalles
		copy(a.Lineas[0][492:], []byte(fmt.Sprintf("%08d", a.Enc.RegistrosTipo2)))
	}
}

func (a *ArchivoPila) ActualizarTipoPlanilla() {
	tPl := a.Enc.TipoPlanilla
	if tPl == "" || tPl == "T" || tPl == "F" || tPl == "P" {
		a.Enc.TipoPlanilla = "E"
		copy(a.Lineas[0][383:], []byte(a.Enc.TipoPlanilla))
	}
}

func (a *ArchivoPila) ActualizarTipoAportante() {

	if a.Enc.TipoAportante == "" {
		a.Enc.TipoAportante = "1"
		copy(a.Lineas[0][244:], []byte(a.Enc.TipoAportante))
	}
}

func (a *ArchivoPila) ActualizarClaseAportante() {

}

func (a *ArchivoPila) ActualizarPresentacion() {
	p := a.Enc.FormaPresentacion
	if p != "U" || p != "C" || p != "D" {
		a.Enc.FormaPresentacion = "U"
		copy(a.Lineas[0][424:], []byte(a.Enc.FormaPresentacion))
	}
}

func (a *ArchivoPila) CambioAVoluntario() {
	if a.Enc.RegistrosTipo2 == 1 {
		if len(a.Det) == 1 {
			det := a.Det[0]
			if det.IdAfiliado == a.Enc.IdAportante {
				a.Enc.TipoAportante = "2"
				a.Enc.TipoPlanilla = "I"
				//a.Enc.ClaseAportante = "I" //@todo El mismo problema de ClaseAportante
				copy(a.Lineas[0][244:], []byte(a.Enc.TipoAportante))
				copy(a.Lineas[0][383:], []byte(a.Enc.TipoPlanilla))
				//Ahora Actualizamos el unico detalle (Lienas[1])
				a.Det[0].TipoCotizante = 3
				copy(a.Lineas[1][24:], []byte(fmt.Sprintf("%02d", a.Det[0].TipoCotizante)))

				if a.Det[0].CotizacionVoluntariaAportante > 0 {
					aporteaf := a.Det[0].CotizacionVoluntariaAf
					sumacot := a.Det[0].CotizacionVoluntariaAportante + aporteaf
					total := aporteaf + sumacot
					a.Det[0].CotizacionVoluntariaAportante = 0
					a.Det[0].CotizacionVoluntariaAf = total

					copy(a.Lineas[1][191:], []byte(fmt.Sprintf("%09d", a.Det[0].CotizacionVoluntariaAportante)))
					copy(a.Lineas[1][182:], []byte(fmt.Sprintf("%09d", a.Det[0].CotizacionVoluntariaAf)))
				}

				if a.Det[0].Ing == "X" {
					a.Det[0].Ing = ""
					copy(a.Lineas[1][135:], []byte(a.Det[0].Ing))
				}

				if a.Det[0].Ret == "X" {
					a.Det[0].Ret = ""
					copy(a.Lineas[1][136:], []byte(a.Det[0].Ret))
				}

			}
		} else {
			log.Fatal(ErrSoloUnDetalleEsperado)
		}
	}
}

func (a *ArchivoPila) ValidarTipoPlanilla() {
	p := a.Enc.TipoPlanilla
	if p == "R" {
		a.MarcaRezago(10)
	}

	if p == "L" {
		a.MarcaRezago(11)
	}

	if p == "Y" {
		tipo := TipoIdEmpresa(a.Enc.TipoId)
		if db.CountEmpresaByIdAndTipo(a.Enc.IdAportante, tipo) == 0 {
			a.MarcaRezago(1)
		}

	}
}

func (a *ArchivoPila) ValidarDatosPlanilla() {

	if a.Enc.TipoId == "" || a.Enc.FormaPresentacion == "" || a.Enc.CodCiudad == "" || a.Enc.CodDep == "" {
		a.MarcaRezago(20)
	}
}

func (a *ArchivoPila) ValidarPeriodo() {

	if a.Enc.Periodo == "" {
		a.MarcaRezago(25)
	}

}

func (a *ArchivoPila) CruzarLogBancario() error {
	base := "SELECT id FROM tbllogbancario WHERE"
	prefix := " AND conciliado=0 and codigobanco = '007' and oficina = 9999"

	f := func(condition string) string {
		return fmt.Sprintf("%s %s %s", base, condition, prefix)
	}

	total := a.Tot39.TotalCot + a.Tot39.Fsp + a.Tot39.Fspsub
	fechapago := strings.Replace(a.Enc.FechaPago, "-", "", -1)
	plani := fmt.Sprintf("%d%d", a.Enc.Operador, a.Enc.Planilla)

	var concilio = 0
	concilio = db.CountLogBancario(f("documentoid=? AND valortotal=? AND fechapago=? AND radicado=?"), a.Enc.IdAportante, total, fechapago, a.Enc.Planilla)
	if concilio != 0 {
		db.UpdateLogBancario(concilio)
		return nil
	}
	concilio = db.CountLogBancario(f("substring(documentoid, 1, length(documentoid) - 1) = ? and valortotal=? and fechapago=? and radicado=?"), a.Enc.IdAportante, total, fechapago, a.Enc.Planilla)
	if concilio != 0 {
		db.UpdateLogBancario(concilio)
		return nil
	}
	concilio = db.CountLogBancario(f("radicado = ? and valortotal=? and fechapago=?"), plani, total, fechapago)
	if concilio != 0 {
		db.UpdateLogBancario(concilio)
		return nil
	}
	concilio = db.CountLogBancario(f("radicado = ? and valortotal=? and fechapago=?"), a.Enc.Planilla, total, fechapago)
	if concilio != 0 {
		db.UpdateLogBancario(concilio)
		return nil
	}
	concilio = db.CountLogBancario(f("substring(documentoid, 1, length(documentoid) - 1) = ? and radicado = ? and valortotal=?"), a.Enc.IdAportante, a.Enc.Planilla, total)
	if concilio != 0 {
		db.UpdateLogBancario(concilio)
		return nil
	}
	concilio = db.CountLogBancario(f("substring(documentoid, 1, length(documentoid) - 1) = ? and radicado = ? and valortotal=?"), a.Enc.IdAportante, plani, total)
	if concilio != 0 {
		db.UpdateLogBancario(concilio)
		return nil
	}
	concilio = db.CountLogBancario("SELECT id FROM tbllogbancario WHERE radicado = ? and valortotal=? and fechapago=? AND conciliado=0 and codigobanco = '007'", plani, total, fechapago)
	if concilio != 0 {
		db.UpdateLogBancario(concilio)
		return nil
	}

	//fmt.Println("uups, not found!")
	a.MarcaRezago(18)

	return nil
	//"radicado='$plani' and valortotal='$total' and conciliado='0' and codigobanco = '007' and fechapago='$arr[fechapago]'",

}

func (a *ArchivoPila) ValidarPlanillaTipoN() {
	if a.Enc.FechaPagoAsociada == "" {
		a.Enc.FechaPagoAsociada = a.Enc.FechaPago
		copy(a.Lineas[0][384:], []byte(fmt.Sprintf("% -10s", a.Enc.FechaPagoAsociada)))
		//@todo validar con Jenry si se tiene que crear el rezago y solucionarlo de una
	}

	if a.Enc.PlanillaAsociada == "" {
		a.MarcaRezago(15)
		//@todo validar para qué actualizar el plano si hay rezago
	}
}

func (a *ArchivoPila) UnificarRegistros() {
	if a.Enc.TipoPlanilla != "N" {
		duplicados := RegistrosDuplicados(a.Det)
		if len(duplicados) != 0 {
			//@todo
		}
	}
}

func (a *ArchivoPila) UnificarRegistrosTipoN() {
	if a.Enc.TipoPlanilla == "N" {
		duplicados := RegistrosDuplicados(a.Det)

		for _, v := range duplicados {
			//fmt.Printf("%s tamaño mapa: %d y el det: %v\n", k, len(a.Det), v)
			if a.Det[v[0]].Correcciones == "" || a.Det[v[1]].Correcciones == "" {
				log.Fatal("No llegó tipo C o Tipo A")
			}

			//fmt.Println("ups")
			tipoA := a.Det[v[0]]
			tipoC := a.Det[v[1]]

			dias := tipoC.Dias - tipoA.Dias
			if dias <= 0 {
				dias = tipoC.Dias
			}

			tipoA.Ibc = tipoC.Ibc - tipoA.Ibc
			tipoA.CotizacionObligatoria = tipoC.CotizacionObligatoria - tipoA.CotizacionObligatoria
			tipoA.CotizacionVoluntariaAf = tipoC.CotizacionVoluntariaAf - tipoA.CotizacionVoluntariaAf
			tipoA.CotizacionVoluntariaAportante = tipoC.CotizacionVoluntariaAportante - tipoA.CotizacionVoluntariaAportante
			tipoA.TotalCot = tipoC.TotalCot - tipoA.TotalCot
			tipoA.Fsp = tipoC.Fsp - tipoA.Fsp
			tipoA.Afsp = tipoC.Afsp - tipoA.Afsp
			tipoA.ValorNoRetenido = tipoC.ValorNoRetenido - tipoA.ValorNoRetenido

			indice := v[0] + 1 // Sumamos uno (Pues en la 0 está el encabezado)
			copy(a.Lineas[indice][157:], []byte(fmt.Sprintf("%010d", tipoA.Ibc)))
			copy(a.Lineas[indice][173:], []byte(fmt.Sprintf("%010d", tipoA.CotizacionObligatoria)))
			copy(a.Lineas[indice][182:], []byte(fmt.Sprintf("%010d", tipoA.CotizacionVoluntariaAf)))
			copy(a.Lineas[indice][191:], []byte(fmt.Sprintf("%010d", tipoA.CotizacionVoluntariaAportante)))
			copy(a.Lineas[indice][200:], []byte(fmt.Sprintf("%010d", tipoA.TotalCot)))
			copy(a.Lineas[indice][209:], []byte(fmt.Sprintf("%010d", tipoA.Fsp)))
			copy(a.Lineas[indice][218:], []byte(fmt.Sprintf("%09d", tipoA.Afsp)))
			copy(a.Lineas[indice][227:], []byte(fmt.Sprintf("%09d", tipoA.ValorNoRetenido)))

		}

		/*
			@todo Esto se puede mejorar?
		*/
		borrados := 0
		for _, v := range duplicados {
			EliminarLineasYEstructuras(a, v[1], borrados)
			borrados += 1
		}

		a.Enc.RegistrosTipo2 = a.Enc.RegistrosTipo2 - borrados
		copy(a.Lineas[0][492:], []byte(fmt.Sprintf("%08d", a.Enc.RegistrosTipo2)))

	}
}

func (d *Detalle) MenoresA30SinNovedad() (bool, int) {
	if d.Dias < 30 {
		if d.Ing == "" && d.Ret == "" && d.Tdp == "" && d.Tap == "" &&
			d.Vsp == "" && d.Vst == "" && d.Sln == "" && d.Ige == "" &&
			d.Lma == "" && d.Vac == "" && d.Avp == "" {
			return true, 12
		}
	}
	return false, 0
}

func (d *Detalle) SalarioIbc() (bool, int) {
	return false, 0
}

func (d *Detalle) ValidarCotizacion() (bool, int) {
	tarifa := d.Tarifa / 100000
	cotiz := d.Ibc * tarifa
	total := d.TotalCot

	if cotiz-100 <= total && cotiz+100 >= total {
		return true, 17
	}

	return false, 0
}

func (d *Detalle) ValidarSubtipoCotizante() (bool, int) {
	if d.TipoCotizante == 18 {
		return true, 13
	}
	return false, 0
}

func (d *Detalle) ValidarValoresEnCero() (bool, int) {
	//@todo se omite valor neto porque ya no aparece en el plano
	if d.Dias == 0 && d.Ibc == 0 && d.CotizacionObligatoria == 0 &&
		d.CotizacionVoluntariaAf == 0 && d.CotizacionVoluntariaAportante == 0 &&
		d.TotalCot == 0 && d.Fsp == 0 && d.Afsp == 0 && (d.Sln == "" || d.Sln == " ") {
		return true, 35
	}
	return false, 0
}

func (a *ArchivoPila) ValidarTresRegistrosDeTotales() {
	if a.TotalTotales != 3 {
		a.Devuelto = true
	}
}

func (a *ArchivoPila) CompararTotales() {
	a.CompararRegistro31()
	a.CompararTotales3136Con39()
}

func (a *ArchivoPila) CompararRegistro31() {
	var ibc,
		cotizacionOblig,
		cotizacionVolAf,
		cotizacionVolApor,
		totalCot,
		fsp,
		afsp int = 0, 0, 0, 0, 0, 0, 0

	for _, v := range a.Det {
		ibc += v.Ibc
		cotizacionOblig += v.CotizacionObligatoria
		cotizacionVolAf += v.CotizacionVoluntariaAf
		cotizacionVolApor += v.CotizacionVoluntariaAportante
		totalCot += v.TotalCot
		fsp += v.Fsp
		afsp += v.Afsp
	}

	if a.Tot31.Ibc != ibc {
		a.Tot31.Ibc = ibc
	}

	if a.Tot31.CotizacionObli != cotizacionOblig {
		a.Tot31.CotizacionObli = cotizacionOblig
	}

	if a.Tot31.CotizacionVoluntariaAf != cotizacionVolAf {
		a.Tot31.CotizacionVoluntariaAf = cotizacionVolAf
	}

	if a.Tot31.CotizacionVoluntariaAportante != cotizacionVolApor {
		a.Tot31.CotizacionVoluntariaAportante = cotizacionVolApor
	}

	if a.Tot31.TotalCot != totalCot {
		a.Tot31.TotalCot = totalCot
	}

	if a.Tot31.Fsp != fsp {
		a.Tot31.Fsp = fsp
	}

	if a.Tot31.Fspsub != afsp {
		a.Tot31.Fspsub = afsp
	}

	//@todo No estoy haciendo el update de a.totalDetalles, por eso aqui estoy leyendo del encabezado. Corregir donde haya lugar
	indice := a.Enc.RegistrosTipo2 + 1

	copy(a.Lineas[indice][6:], []byte(fmt.Sprintf("%010d", a.Tot31.Ibc)))
	copy(a.Lineas[indice][16:], []byte(fmt.Sprintf("%010d", a.Tot31.CotizacionObli)))
	copy(a.Lineas[indice][26:], []byte(fmt.Sprintf("%010d", a.Tot31.CotizacionVoluntariaAf)))
	copy(a.Lineas[indice][36:], []byte(fmt.Sprintf("%010d", a.Tot31.CotizacionVoluntariaAportante)))
	copy(a.Lineas[indice][46:], []byte(fmt.Sprintf("%010d", a.Tot31.TotalCot)))
	copy(a.Lineas[indice][56:], []byte(fmt.Sprintf("%010d", a.Tot31.Fsp)))
	copy(a.Lineas[indice][66:], []byte(fmt.Sprintf("%010d", a.Tot31.Fspsub)))

}

func (a *ArchivoPila) CompararTotales3136Con39() {

	totalCto := a.Tot31.TotalCot + a.Tot36.MoraCotizaciones
	totalFsp := a.Tot31.Fsp + a.Tot36.MoraFsp
	totalFspsub := a.Tot31.Fspsub + a.Tot36.MoraFspsub

	/* @todo Preguntar a Jenry que es lo que hay que hacer aqui
	porque en el php no se entiende */
	if a.Tot39.TotalCot != totalCto {

	}

	if a.Tot39.Fsp != totalFsp {

	}

	if a.Tot39.Fspsub != totalFspsub {

	}

}

func (a *ArchivoPila) TipoUno(l string) {

	nroRegistro, err := strconv.Atoi(l[0:4])
	if err != nil {
		log.Fatal(err)
	}
	tipoRegistro, err := strconv.Atoi(strings.TrimSpace(l[5:6]))
	if err != nil {
		log.Fatal(err)
	}
	codFormato, err := strconv.Atoi(l[6:8])
	if err != nil {
		log.Fatal(err)
	}
	idAfp, err := strconv.Atoi(strings.TrimSpace(l[8:24]))
	if err != nil {
		log.Fatal(err)
	}
	digito, err := strconv.Atoi(l[24:25])
	if err != nil {
		log.Fatal(err)
	}

	planilla, err := strconv.Atoi(strings.TrimSpace(l[414:424]))
	if err != nil {
		log.Fatal(err)
	}

	totalEmpleados, err := strconv.Atoi(strings.TrimSpace(l[475:480]))
	if err != nil {
		log.Fatal(err)
	}

	afiliados, err := strconv.Atoi(strings.TrimSpace(l[480:485]))
	if err != nil {
		log.Fatal(err)
	}

	operador, err := strconv.Atoi(strings.TrimSpace(l[485:487]))
	if err != nil {
		log.Fatal(err)
	}

	diasMora, err := strconv.Atoi(strings.TrimSpace(l[488:492]))
	if err != nil {
		log.Fatal(err)
	}

	modalidad, err := strconv.Atoi(strings.TrimSpace(l[487:488]))
	if err != nil {
		log.Fatal(err)
	}

	registrosTipo2, err := strconv.Atoi(strings.TrimSpace(l[492:500]))
	if err != nil {
		log.Fatal(err)
	}

	enc := &Encabezado{
		NroRegistro:       nroRegistro,
		TipoRegistro:      tipoRegistro,
		CodFormato:        codFormato,
		IdAfp:             idAfp,
		Digito:            digito,
		RazonSocial:       strings.TrimSpace(l[25:225]),
		TipoId:            l[225:227],
		IdAportante:       strings.TrimSpace(l[227:243]),
		DigitoAportante:   l[243:244],
		Direccion:         strings.TrimSpace(l[245:285]),
		CodCiudad:         strings.TrimSpace(l[285:288]),
		CodDep:            strings.TrimSpace(l[288:290]),
		Telefono:          strings.TrimSpace(l[290:300]),
		Fax:               strings.TrimSpace(l[300:310]),
		Mail:              strings.TrimSpace(l[310:370]),
		Periodo:           l[370:377],
		FechaPago:         l[394:404],
		Planilla:          planilla,
		FormaPresentacion: l[424:425],
		CodSucursal:       strings.TrimSpace(l[425:435]),
		NomSucursal:       strings.TrimSpace(l[435:475]),
		TotalEmpleados:    totalEmpleados,
		Afiliados:         afiliados,
		Operador:          operador,
		TipoAportante:     l[244:245],
		Arp:               l[377:383],
		TipoPlanilla:      l[383:384],
		FechaPagoAsociada: strings.TrimSpace(l[384:394]),
		PlanillaAsociada:  strings.TrimSpace(l[404:414]),
		DiasMora:          diasMora,
		Modalidad:         modalidad,
		RegistrosTipo2:    registrosTipo2,
	}

	a.Enc = enc

}

func (a *ArchivoPila) TipoDos(l string) {

	nroRegistro, err := strconv.Atoi(l[0:4])
	if err != nil {
		log.Fatal(err)
	}

	tipoRegistro, err := strconv.Atoi(l[5:6])
	if err != nil {
		log.Fatal(err)
	}

	tipoCotizante, err := strconv.Atoi(l[24:26])
	if err != nil {
		log.Fatal(err)
	}

	subtipoCotizante, err := strconv.Atoi(l[26:28])
	if err != nil {
		log.Fatal(err)
	}

	dias, err := strconv.Atoi(l[146:148])
	if err != nil {
		log.Fatal(err)
	}

	salario, err := strconv.Atoi(l[148:157])
	if err != nil {
		log.Fatal(err)
	}

	ibc, err := strconv.Atoi(l[157:166])
	if err != nil {
		log.Fatal(err)
	}

	tarifa, err := strconv.Atoi(l[168:173])
	if err != nil {
		log.Fatal(err)
	}

	cotizacionObligatoria, err := strconv.Atoi(l[173:182])
	if err != nil {
		log.Fatal(err)
	}

	cotizacionVoluntariaAf, err := strconv.Atoi(l[182:191])
	if err != nil {
		log.Fatal(err)
	}

	cotizacionVoluntariaAportante, err := strconv.Atoi(l[191:200])
	if err != nil {
		log.Fatal(err)
	}

	totalCot, err := strconv.Atoi(l[200:209])
	if err != nil {
		log.Fatal(err)
	}

	fsp, err := strconv.Atoi(l[209:219])
	if err != nil {
		log.Fatal(err)
	}

	afsp, err := strconv.Atoi(l[218:227])
	if err != nil {
		log.Fatal(err)
	}

	valorNoRetenido, err := strconv.Atoi(l[227:236])
	if err != nil {
		log.Fatal(err)
	}

	a.ValidarPuntoEnTipoDos(l[167:168])

	det := &Detalle{
		NroRegistro:                   nroRegistro,
		TipoRegistro:                  tipoRegistro,
		TipoDocumento:                 l[6:8],
		IdAfiliado:                    strings.TrimSpace(l[8:24]),
		TipoCotizante:                 tipoCotizante,
		SubtipoCotizante:              subtipoCotizante,
		Extanjero:                     l[28:29],
		ColExterior:                   l[29:30],
		CodDepartamento:               l[30:32],
		CodMunicipio:                  l[32:35],
		PrimerApellido:                strings.TrimSpace(l[35:55]),
		SegundoApellido:               strings.TrimSpace(l[55:85]),
		PrimerNombre:                  strings.TrimSpace(l[85:105]),
		SegundoNombre:                 strings.TrimSpace(l[105:135]),
		Ing:                           l[135:136],
		Ret:                           l[136:137],
		Tdp:                           l[137:138],
		Tap:                           l[138:139],
		Vsp:                           l[139:140],
		Vst:                           l[140:141],
		Sln:                           l[141:142],
		Ige:                           l[142:143],
		Lma:                           l[143:144],
		Vac:                           l[144:145],
		Avp:                           l[145:146],
		Dias:                          dias,
		Salario:                       salario,
		Ibc:                           ibc,
		Tarifa:                        tarifa,
		CotizacionObligatoria:         cotizacionObligatoria,
		CotizacionVoluntariaAf:        cotizacionVoluntariaAf,
		CotizacionVoluntariaAportante: cotizacionVoluntariaAportante,
		TotalCot:                      totalCot,
		Fsp:                           fsp,
		Afsp:                          afsp,
		ValorNoRetenido:               valorNoRetenido,
		Correcciones:                  l[236:237],
		SalarioIntegral:               l[237:238],
	}

	a.Det = append(a.Det, det)

}

func (a *ArchivoPila) TipoTres(l string) {

	tipo := l[0:5]
	if tipo == "00031" {
		totalAportes, err := strconv.Atoi(l[0:4])
		if err != nil {
			log.Fatal(err)
		}

		tipoRegistro, err := strconv.Atoi(l[5:6])
		if err != nil {
			log.Fatal(err)
		}

		ibc, err := strconv.Atoi(l[6:16])
		if err != nil {
			log.Fatal(err)
		}

		cotizacionObli, err := strconv.Atoi(l[16:26])
		if err != nil {
			log.Fatal(err)
		}

		cotizacionVoluntariaAf, err := strconv.Atoi(l[26:36])
		if err != nil {
			log.Fatal(err)
		}

		cotizacionVoluntariaAportante, err := strconv.Atoi(l[36:46])
		if err != nil {
			log.Fatal(err)
		}

		totalCot, err := strconv.Atoi(l[46:56])
		if err != nil {
			log.Fatal(err)
		}

		fsp, err := strconv.Atoi(l[56:66])
		if err != nil {
			log.Fatal(err)
		}

		fspsub, err := strconv.Atoi(l[66:76])
		if err != nil {
			log.Fatal(err)
		}

		tot31 := &Totales31{
			TotalAportes:                  totalAportes,
			TipoRegistro:                  tipoRegistro,
			Ibc:                           ibc,
			CotizacionObli:                cotizacionObli,
			CotizacionVoluntariaAf:        cotizacionVoluntariaAf,
			CotizacionVoluntariaAportante: cotizacionVoluntariaAportante,
			TotalCot:                      totalCot,
			Fsp:                           fsp,
			Fspsub:                        fspsub,
		}

		a.Tot31 = tot31

	}

	if tipo == "00036" {

		tipoRegistro, err := strconv.Atoi(l[5:6])
		if err != nil {
			log.Fatal(err)
		}

		diasMora, err := strconv.Atoi(l[6:10])
		if err != nil {
			log.Fatal(err)
		}

		moraCotizaciones, err := strconv.Atoi(l[10:20])
		if err != nil {
			log.Fatal(err)
		}

		moraFsp, err := strconv.Atoi(l[20:30])
		if err != nil {
			log.Fatal(err)
		}

		moraFspsub, err := strconv.Atoi(l[30:40])
		if err != nil {
			log.Fatal(err)
		}

		tot36 := &Totales36{
			Intereses:                     0, //@todo Error en el php, preguntar a jenry
			TipoRegistro:                  tipoRegistro,
			Ibc:                           0,
			CotizacionObli:                0,
			CotizacionVoluntariaAf:        0,
			CotizacionVoluntariaAportante: 0,
			TotalCot:                      0,
			Fsp:                           0,
			Fspsub:                        0,
			DiasMora:                      diasMora,
			MoraCotizaciones:              moraCotizaciones,
			MoraFsp:                       moraFsp,
			MoraFspsub:                    moraFspsub,
		}

		a.Tot36 = tot36

	}

	if tipo == "00039" {

		tipoRegistro, err := strconv.Atoi(l[5:6])
		if err != nil {
			log.Fatal(err)
		}

		totalCot, err := strconv.Atoi(l[6:16])
		if err != nil {
			log.Fatal(err)
		}

		fsp, err := strconv.Atoi(l[16:26])
		if err != nil {
			log.Fatal(err)
		}

		fspsub, err := strconv.Atoi(l[26:36])
		if err != nil {
			log.Fatal(err)
		}

		tot39 := &Totales39{
			TotalAPagar:                   0, //@todo Error en el php, preguntar a jenry
			TipoRegistro:                  tipoRegistro,
			Ibc:                           0,
			CotizacionObli:                0,
			CotizacionVoluntariaAf:        0,
			CotizacionVoluntariaAportante: 0,
			TotalCot:                      totalCot,
			Fsp:                           fsp,
			Fspsub:                        fspsub,
		}

		a.Tot39 = tot39

	}

}

package database

import (
	"database/sql"
	"fmt"
	_ "github.com/bmizerany/pq"
	"github.com/coocood/qbs"
	"log"
	"os"
)

type EmpleadoresCes struct {
	Identificacion string
	Tipoidentif    string
}

func RegisterDb() {
	spec := fmt.Sprintf("host=%s user=%s password=%s dbname=%s", os.Getenv("PILAHOST"),
		os.Getenv("PILAUSER"),
		os.Getenv("PILAPASS"),
		os.Getenv("PILADB"))

	qbs.Register("postgres", spec, "llamadas_cesantias-old", qbs.NewPostgres())
}

func ConnDb() (q *qbs.Qbs, err error) {
	RegisterDb()
	q, err = qbs.GetQbs()
	return q, err
}

func CountEmpresaByIdAndTipo(id string, tipo string) int64 {
	q, _ := ConnDb()
	defer q.Close()

	emp := new(EmpleadoresCes)

	condition := qbs.NewCondition("identificacion=?", id).And("tipoidentif=?", tipo)
	cuantos := q.Condition(condition).Count(&emp)

	return cuantos

}

func CountLogBancario(query string, args ...interface{}) int {
	q, _ := ConnDb()

	var id int
	err := q.QueryRow(query, args...).Scan(&id)
	//err := q.QueryRow("select id from tbllogbancario where documentoid = ? and fechapago = ? and valorefectivo = ? and radicado = ? AND conciliado=0 and codigobanco = '007' and oficina = 9999", "319706042", "20130305", 94620, 8939087162).Scan(&id)
	//fmt.Printf("%d", id)
	if err == sql.ErrNoRows {
		//fmt.Printf("%+v\n", args...)
		//fmt.Println("no shit men")
		return 0
	}

	if err != nil {
		log.Fatal(err)
	}

	defer q.Close()
	return id
}

func UpdateLogBancario(id int) {
	q, _ := ConnDb()
	defer q.Close()

	type Tbllogbancario struct {
		Conciliado int
	}

	log := new(Tbllogbancario)
	log.Conciliado = 1
	q.WhereEqual("id", id).Update(log)
}

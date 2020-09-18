<!-- <?php
	// $i=1;
	// foreach ($list->result() as $row) {
	// 	echo "$i $row->Nombres <br>";
	// 	$i++;
	// }
 ?> -->
<br>
 <table class="table table-bordered">
		<thead>
			<tr>
				<th>Num</th>
				<th align="center">Categorías</th>
				<th>Apellidos y Nombres</th>
				<th align="center">Reporte de Investigación</th>
			</tr>
		</thead>
		<tbody>
		<?php 
			$i=1; 
			foreach($list->result() as $row){

				// if($row->Estado==0) $estado="<br><small style='color:red;'>Esta Linea de Investigación se encuentra desbilitada.</small>";
				$cat=$this->dbRepo->getOneField("dicCategorias","Nombre","Id=$row->IdCategoria");
				$est=$this->dbRepo->getOneField("dicEstadosDoc","Nombre","Id=$row->Activo");
				$color=($row->Activo==5)? "color: green;":"color:red;";
				$color=($row->Activo==6)? "color: green;":"color:red;";
				echo "<tr>
						<td align='center'>$i</td>
						<td align='center' style='font-size:10px'>".$cat."</td>
						<td>$row->Apellidos , $row->Nombres</td>
						<td align='center'>
						
						<a href='".base_url("pilar/cordinads/EvalDocente/$row->Id")."' target=_blank class='btn btn-info btn-xs'>Reporte</a>
						<a href='".base_url("pilar/cordinads/EvalDocenteAnio/$row->Id/2018")."' target=_blank class='btn btn-info btn-xs'>Reporte 2018</a>
						<a href='".base_url("pilar/cordinads/EvalDocenteAnio/$row->Id/2019")."' target=_blank class='btn btn-success btn-xs'>Reporte 2019</a>

						</td>
					  </tr>";
				$i++;
			}  
		?>
		</tbody>
	</table> 
 <?php require_once 'src/views/components/topbar.php'; ?>

<div class="container-fluid p-0">
    <div class="row flex-nowrap m-0">
        <?php require_once 'src/views/components/sidebar.php'; ?>
        <main class="col-12 col-md-9 col-lg-10 ms-auto px-2 px-md-4 pt-3 pt-md-4 pb-5 d-flex flex-column min-vh-100">
            <div class="container-fluid p-0">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
                    <h1 class="h2">Directorio de Proveedores</h1>
                    <?php if ($rol === 'presidente') : ?>
                        <div class="btn-toolbar mb-2 mb-md-0">
                            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalCrearProveedor">
                                <i class="fa-solid fa-plus me-2"></i>Añadir Proveedor
                            </button>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Buscador -->
                <div class="mb-4">
                    <input type="text" id="buscadorProveedores" class="form-control form-control-lg" placeholder="🔍 Buscar por nombre, categoría o teléfono...">
                </div>

                <!-- Contenedor de Proveedores -->
                <div id="listaProveedores">
                    <?php if (empty($proveedoresAgrupados)) : ?>
                        <div class="alert alert-info text-center">No hay proveedores registrados en la comunidad.</div>
                    <?php else : ?>
                        <?php foreach ($proveedoresAgrupados as $categoria => $proveedores) : ?>
                            <div class="categoria-grupo mb-5">
                                <h3 class="mb-3 border-bottom pb-2 font-title"><?= htmlspecialchars($categoria) ?></h3>
                                <div class="row row-cols-1 row-cols-lg-2 row-cols-xl-3 g-4">
                                    <?php foreach ($proveedores as $p) : ?>
                                        <div class="col proveedor-card">
                                            <div class="card h-100 shadow-sm">
                                                <div class="card-body">
                                                    <div class="d-flex justify-content-between align-items-start">
                                                        <h5 class="card-title fw-bold text-primary"><?= htmlspecialchars($p['nombre']) ?></h5>
                                                        <?php if ($rol === 'presidente') : ?>
                                                            <div class="dropdown">
                                                                <button class="btn btn-sm" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                                    <i class="fa-solid fa-ellipsis-vertical"></i>
                                                                </button>
                                                                <ul class="dropdown-menu dropdown-menu-end">
                                                                    <li><a class="dropdown-item" href="#" onclick="abrirModalEditar(<?= htmlspecialchars(json_encode($p)) ?>)">Editar</a></li>
                                                                    <li><a class="dropdown-item text-danger" href="#" onclick="abrirModalEliminar(<?= $p['id_proveedor'] ?>, '<?= htmlspecialchars($p['nombre']) ?>')">Eliminar</a></li>
                                                                </ul>
                                                            </div>
                                                        <?php endif; ?>
                                                    </div>

                                                    <?php if (!empty($p['descripcion'])) : ?>
                                                        <p class="card-text text-muted small mb-3"><?= htmlspecialchars($p['descripcion']) ?></p>
                                                    <?php endif; ?>

                                                    <ul class="list-unstyled">
                                                        <?php if (!empty($p['telefono'])) : ?>
                                                            <li class="mb-2 d-flex align-items-center">
                                                                <i class="fa-solid fa-phone fa-fw me-2 text-muted"></i>
                                                                <a href="tel:<?= htmlspecialchars($p['telefono']) ?>" class="text-decoration-none text-reset"><?= htmlspecialchars($p['telefono']) ?></a>
                                                            </li>
                                                        <?php endif; ?>
                                                        <?php if (!empty($p['email'])) : ?>
                                                            <li class="mb-2 d-flex align-items-center">
                                                                <i class="fa-solid fa-envelope fa-fw me-2 text-muted"></i>
                                                                <a href="mailto:<?= htmlspecialchars($p['email']) ?>" class="text-decoration-none text-reset"><?= htmlspecialchars($p['email']) ?></a>
                                                            </li>
                                                        <?php endif; ?>
                                                        <?php if (!empty($p['horario'])) : ?>
                                                            <li class="d-flex align-items-center">
                                                                <i class="fa-solid fa-clock fa-fw me-2 text-muted"></i>
                                                                <span><?= htmlspecialchars($p['horario']) ?></span>
                                                            </li>
                                                        <?php endif; ?>
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                <div id="noResultados" class="alert alert-warning text-center" style="display: none;">No se encontraron proveedores que coincidan con la búsqueda.</div>
            </div>
            </main>
    </div>
</div>
    <?php if ($rol === 'presidente') : ?>
        <!-- Modal Crear Proveedor -->
        <div class="modal fade" id="modalCrearProveedor" tabindex="-1" aria-labelledby="modalCrearProveedorLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalCrearProveedorLabel">Añadir Nuevo Proveedor</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form action="index.php?route=proveedor/store" method="POST">
                        <div class="modal-body">
                            <div class="mb-3">
                                <label for="nombre" class="form-label">Nombre o Empresa</label>
                                <input type="text" class="form-control" id="nombre" name="nombre" required>
                            </div>
                            <div class="mb-3">
                                <label for="categoria" class="form-label">Categoría</label>
                                <select class="form-select" id="categoria" name="categoria" required>
                                    <option value="Conserjería">Conserjería</option>
                                    <option value="Limpieza">Limpieza</option>
                                    <option value="Fontanería">Fontanería</option>
                                    <option value="Electricidad">Electricidad</option>
                                    <option value="Ascensores">Ascensores</option>
                                    <option value="Jardinería">Jardinería</option>
                                    <option value="Emergencias">Emergencias</option>
                                    <option value="Otros">Otros</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="telefono" class="form-label">Teléfono</label>
                                <input type="tel" class="form-control" id="telefono" name="telefono" pattern="[0-9]{9}" title="El teléfono debe contener 9 dígitos." maxlength="9">
                            </div>
                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" name="email" title="Introduce un formato de correo válido (ej: usuario@dominio.com)">
                            </div>
                            <div class="mb-3">
                                <label for="horario" class="form-label">Horario</label>
                                <input type="text" class="form-control" id="horario" name="horario" placeholder="Ej: L-V de 9:00 a 18:00">
                            </div>
                            <div class="mb-3">
                                <label for="descripcion" class="form-label">Descripción (opcional)</label>
                                <textarea class="form-control" id="descripcion" name="descripcion" rows="3"></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                            <button type="submit" class="btn btn-primary">Guardar Proveedor</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Modal Editar Proveedor -->
        <div class="modal fade" id="modalEditarProveedor" tabindex="-1" aria-labelledby="modalEditarProveedorLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalEditarProveedorLabel">Editar Proveedor</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form action="index.php?route=proveedor/update" method="POST">
                        <input type="hidden" id="edit_id_proveedor" name="id_proveedor">
                        <div class="modal-body">
                            <div class="mb-3">
                                <label for="edit_nombre" class="form-label">Nombre o Empresa</label>
                                <input type="text" class="form-control" id="edit_nombre" name="nombre" required>
                            </div>
                            <div class="mb-3">
                                <label for="edit_categoria" class="form-label">Categoría</label>
                                <select class="form-select" id="edit_categoria" name="categoria" required>
                                    <option value="Conserjería">Conserjería</option>
                                    <option value="Limpieza">Limpieza</option>
                                    <option value="Fontanería">Fontanería</option>
                                    <option value="Electricidad">Electricidad</option>
                                    <option value="Ascensores">Ascensores</option>
                                    <option value="Jardinería">Jardinería</option>
                                    <option value="Emergencias">Emergencias</option>
                                    <option value="Otros">Otros</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="edit_telefono" class="form-label">Teléfono</label>
                                <input type="tel" class="form-control" id="edit_telefono" name="telefono" pattern="[0-9]{9}" title="El teléfono debe contener 9 dígitos." maxlength="9">
                            </div>
                            <div class="mb-3">
                                <label for="edit_email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="edit_email" name="email" title="Introduce un formato de correo válido (ej: usuario@dominio.com)">
                            </div>
                            <div class="mb-3">
                                <label for="edit_horario" class="form-label">Horario</label>
                                <input type="text" class="form-control" id="edit_horario" name="horario">
                            </div>
                            <div class="mb-3">
                                <label for="edit_descripcion" class="form-label">Descripción (opcional)</label>
                                <textarea class="form-control" id="edit_descripcion" name="descripcion" rows="3"></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                            <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Modal Eliminar Proveedor -->
        <div class="modal fade" id="modalEliminarProveedor" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Confirmar Eliminación</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p>¿Estás seguro de que quieres eliminar al proveedor <strong id="nombreProveedorEliminar"></strong>? Esta acción no se puede deshacer.</p>
                    </div>
                    <div class="modal-footer">
                        <form action="index.php?route=proveedor/destroy" method="POST">
                            <input type="hidden" id="id_proveedor_eliminar" name="id_proveedor_eliminar">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                            <button type="submit" class="btn btn-danger">Eliminar</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

    <?php endif; ?>

    <script>
        <?php if ($rol === 'presidente') : ?>
            // Funciones para modales
            function abrirModalEditar(proveedor) {
                document.getElementById('edit_id_proveedor').value = proveedor.id_proveedor;
                document.getElementById('edit_nombre').value = proveedor.nombre;
                document.getElementById('edit_categoria').value = proveedor.categoria;
                document.getElementById('edit_telefono').value = proveedor.telefono;
                document.getElementById('edit_email').value = proveedor.email;
                document.getElementById('edit_horario').value = proveedor.horario;
                document.getElementById('edit_descripcion').value = proveedor.descripcion;
                new bootstrap.Modal(document.getElementById('modalEditarProveedor')).show();
            }

            function abrirModalEliminar(id, nombre) {
                document.getElementById('id_proveedor_eliminar').value = id;
                document.getElementById('nombreProveedorEliminar').textContent = nombre;
                new bootstrap.Modal(document.getElementById('modalEliminarProveedor')).show();
            }
        <?php endif; ?>

        // Buscador en tiempo real
        document.addEventListener('DOMContentLoaded', function() {
            const buscador = document.getElementById('buscadorProveedores');
            const noResultados = document.getElementById('noResultados');

            buscador.addEventListener('keyup', function() {
                const termino = buscador.value.toLowerCase();
                const grupos = document.querySelectorAll('.categoria-grupo');
                let resultadosVisibles = 0;

                grupos.forEach(function(grupo) {
                    const tarjetas = grupo.querySelectorAll('.proveedor-card');
                    let tarjetasVisiblesEnGrupo = 0;

                    tarjetas.forEach(function(tarjeta) {
                        const contenido = tarjeta.textContent.toLowerCase();
                        if (contenido.includes(termino)) {
                            tarjeta.style.display = '';
                            tarjetasVisiblesEnGrupo++;
                        } else {
                            tarjeta.style.display = 'none';
                        }
                    });

                    if (tarjetasVisiblesEnGrupo > 0) {
                        grupo.style.display = '';
                        resultadosVisibles += tarjetasVisiblesEnGrupo;
                    } else {
                        grupo.style.display = 'none';
                    }
                });

                if (resultadosVisibles === 0 && termino.length > 0) {
                    noResultados.style.display = 'block';
                } else {
                    noResultados.style.display = 'none';
                }
            });
        });
    </script>
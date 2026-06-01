<?php require_once 'src/views/components/topbar.php'; ?>

<div class="container-fluid p-0">
    <div class="row flex-nowrap m-0">
        <?php require_once 'src/views/components/sidebar.php'; ?>
        <main class="col-12 col-md-9 col-lg-10 ms-auto px-2 px-md-4 pt-3 pt-md-4 pb-5 d-flex flex-column min-vh-100">
            <div class="container-fluid p-0">
                <!-- Cabecera -->
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
                    <div>
                        <h2 class="fw-bold mb-1 font-title">Proveedores</h2>
                        <p class="text-muted small mb-0">Directorio de contactos y servicios de la comunidad</p>
                    </div>
                    <?php if ($rol === 'presidente') : ?>
                        <button type="button" class="btn btn-success text-white fw-semibold shadow-sm d-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#modalCrearProveedor">
                            <i class="fa-solid fa-plus"></i> Añadir Proveedor
                        </button>
                    <?php endif; ?>
                </div>

                <!-- Buscador -->
                <div class="mb-4" style="max-width: 450px;">
                    <div class="input-group shadow-sm" style="border-radius: var(--radio-md); overflow: hidden; border: 1px solid var(--color-borde);">
                        <span class="input-group-text border-0" style="background-color: var(--color-fondo-formularios); color: var(--color-texto);">
                            <i class="bi bi-search"></i>
                        </span>
                        <input type="text" id="buscadorProveedores" class="form-control border-0" 
                               placeholder="Buscar por nombre, categoría o teléfono..." 
                               style="background-color: var(--color-fondo-formularios); font-size: 14px; color: var(--bs-dark); height: 44px;">
                    </div>
                </div>

                <!-- Contenedor de Proveedores -->
                <div id="listaProveedores">
                    <?php if (empty($proveedoresAgrupados)) : ?>
                        <div class="card border-0 shadow-sm module-card bg-light rounded-3">
                            <div class="card-body p-5 text-center text-muted">
                                <i class="fa-solid fa-address-book fs-1 d-block mb-3 opacity-50"></i>
                                <h5 class="fw-bold font-title">No hay proveedores registrados</h5>
                                <p class="small mb-0">No se encontraron contactos en el directorio de la comunidad.</p>
                            </div>
                        </div>
                    <?php else : ?>
                        <?php foreach ($proveedoresAgrupados as $categoria => $proveedores) : ?>
                            <div class="categoria-grupo mb-5">
                                <h4 class="mb-3 border-bottom pb-2 font-title text-dark"><?= htmlspecialchars($categoria) ?></h4>
                                <div class="row row-cols-1 row-cols-lg-2 row-cols-xl-3 g-4">
                                    <?php foreach ($proveedores as $p) : ?>
                                        <div class="col proveedor-card">
                                            <div class="card h-100 shadow-sm border-0 module-card bg-light rounded-3">
                                                <div class="card-body p-4">
                                                    <div class="d-flex justify-content-between align-items-start">
                                                        <h5 class="card-title fw-bold text-dark font-title mb-1"><?= htmlspecialchars($p['nombre']) ?></h5>
                                                        <?php if ($rol === 'presidente') : ?>
                                                            <div class="dropdown">
                                                                <button class="btn btn-link text-muted p-0 text-decoration-none shadow-none" type="button" data-bs-toggle="dropdown" aria-expanded="false" title="Opciones">
                                                                    <i class="fa-solid fa-ellipsis-vertical"></i>
                                                                </button>
                                                                <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0">
                                                                    <li><a class="dropdown-item" href="#" onclick="abrirModalEditar(<?= htmlspecialchars(json_encode($p)) ?>)"><i class="fa-solid fa-pen me-2"></i> Editar</a></li>
                                                                    <li><a class="dropdown-item text-danger" href="#" onclick="abrirModalEliminar(<?= $p['id_proveedor'] ?>, '<?= htmlspecialchars($p['nombre']) ?>')"><i class="fa-solid fa-trash me-2"></i> Eliminar</a></li>
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
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content bg-fondo rounded-4 border-0">
                    <div class="modal-header border-0 pb-0">
                        <h5 class="modal-title fw-bold text-dark font-title" id="modalCrearProveedorLabel">Añadir Nuevo Proveedor</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form action="index.php?route=proveedor/store" method="POST">
                        <div class="modal-body">
                            <div class="mb-3">
                                <label for="nombre" class="form-label small fw-semibold text-muted">Nombre o Empresa</label>
                                <input type="text" class="form-control bg-light border-custom" id="nombre" name="nombre" required>
                            </div>
                            <div class="mb-3">
                                <label for="categoria" class="form-label small fw-semibold text-muted">Categoría</label>
                                <select class="form-select bg-light border-custom" id="categoria" name="categoria" required>
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
                                <label for="telefono" class="form-label small fw-semibold text-muted">Teléfono</label>
                                <input type="tel" class="form-control bg-light border-custom" id="telefono" name="telefono" pattern="[0-9]{9}" title="El teléfono debe contener 9 dígitos." maxlength="9">
                            </div>
                            <div class="mb-3">
                                <label for="email" class="form-label small fw-semibold text-muted">Email</label>
                                <input type="email" class="form-control bg-light border-custom" id="email" name="email" title="Introduce un formato de correo válido (ej: usuario@dominio.com)">
                            </div>
                            <div class="mb-3">
                                <label for="horario" class="form-label small fw-semibold text-muted">Horario</label>
                                <input type="text" class="form-control bg-light border-custom" id="horario" name="horario" placeholder="Ej: L-V de 9:00 a 18:00">
                            </div>
                            <div class="mb-3">
                                <label for="descripcion" class="form-label small fw-semibold text-muted">Descripción (opcional)</label>
                                <textarea class="form-control bg-light border-custom" id="descripcion" name="descripcion" rows="3"></textarea>
                            </div>
                        </div>
                        <div class="modal-footer border-0 pt-0">
                            <button type="button" class="btn btn-outline-secondary fw-semibold shadow-sm" data-bs-dismiss="modal">Cancelar</button>
                            <button type="submit" class="btn btn-success text-white fw-semibold shadow-sm d-flex align-items-center gap-2"><i class="fa-solid fa-floppy-disk"></i> Guardar Proveedor</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Modal Editar Proveedor -->
        <div class="modal fade" id="modalEditarProveedor" tabindex="-1" aria-labelledby="modalEditarProveedorLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content bg-fondo rounded-4 border-0">
                    <div class="modal-header border-0 pb-0">
                        <h5 class="modal-title fw-bold text-dark font-title" id="modalEditarProveedorLabel">Editar Proveedor</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form action="index.php?route=proveedor/update" method="POST">
                        <input type="hidden" id="edit_id_proveedor" name="id_proveedor">
                        <div class="modal-body">
                            <div class="mb-3">
                                <label for="edit_nombre" class="form-label small fw-semibold text-muted">Nombre o Empresa</label>
                                <input type="text" class="form-control bg-light border-custom" id="edit_nombre" name="nombre" required>
                            </div>
                            <div class="mb-3">
                                <label for="edit_categoria" class="form-label small fw-semibold text-muted">Categoría</label>
                                <select class="form-select bg-light border-custom" id="edit_categoria" name="categoria" required>
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
                                <label for="edit_telefono" class="form-label small fw-semibold text-muted">Teléfono</label>
                                <input type="tel" class="form-control bg-light border-custom" id="edit_telefono" name="telefono" pattern="[0-9]{9}" title="El teléfono debe contener 9 dígitos." maxlength="9">
                            </div>
                            <div class="mb-3">
                                <label for="edit_email" class="form-label small fw-semibold text-muted">Email</label>
                                <input type="email" class="form-control bg-light border-custom" id="edit_email" name="email" title="Introduce un formato de correo válido (ej: usuario@dominio.com)">
                            </div>
                            <div class="mb-3">
                                <label for="edit_horario" class="form-label small fw-semibold text-muted">Horario</label>
                                <input type="text" class="form-control bg-light border-custom" id="edit_horario" name="horario">
                            </div>
                            <div class="mb-3">
                                <label for="edit_descripcion" class="form-label small fw-semibold text-muted">Descripción (opcional)</label>
                                <textarea class="form-control bg-light border-custom" id="edit_descripcion" name="descripcion" rows="3"></textarea>
                            </div>
                        </div>
                        <div class="modal-footer border-0 pt-0">
                            <button type="button" class="btn btn-outline-secondary fw-semibold shadow-sm" data-bs-dismiss="modal">Cancelar</button>
                            <button type="submit" class="btn btn-primary fw-semibold shadow-sm d-flex align-items-center gap-2"><i class="fa-solid fa-floppy-disk"></i> Guardar Cambios</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Modal Eliminar Proveedor -->
        <div class="modal fade" id="modalEliminarProveedor" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content bg-light rounded-4 border-0">
                    <div class="modal-header border-0 pb-0">
                        <h5 class="modal-title fw-bold text-danger font-title"><i class="fa-solid fa-triangle-exclamation me-2"></i>Eliminar Proveedor</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body py-1 mt-3">
                        <p class="mb-0 fs-5 text-dark">¿Estás seguro de que quieres eliminar al proveedor <strong id="nombreProveedorEliminar" class="text-primary"></strong>?</p>
                        <p class="text-muted small mt-2 mb-0">Esta acción no se puede deshacer.</p>
                    </div>
                    <div class="modal-footer border-0">
                        <form action="index.php?route=proveedor/destroy" method="POST" class="w-100 d-flex justify-content-end gap-2 m-0">
                            <input type="hidden" id="id_proveedor_eliminar" name="id_proveedor_eliminar">
                            <button type="button" class="btn btn-outline-secondary fw-semibold shadow-sm" data-bs-dismiss="modal">Cancelar</button>
                            <button type="submit" class="btn btn-danger fw-semibold shadow-sm d-flex align-items-center gap-2"><i class="fa-solid fa-trash"></i> Sí, eliminar</button>
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
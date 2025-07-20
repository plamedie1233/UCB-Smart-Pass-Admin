<?php
/**
 * Gestion des étudiants avec Vue.js
 * SmartAccess UCB - Université Catholique de Bukavu
 */

require_once '../includes/session.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';

// Vérification de l'authentification
requireAdmin();

$admin = getLoggedAdmin();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Étudiants - SmartAccess UCB</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; }
        .navbar {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .content-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            overflow: hidden;
        }
        .content-card .card-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 1rem 1.5rem;
        }
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
        }
        .btn-primary:hover {
            background: linear-gradient(135deg, #5a6fd8 0%, #6a4190 100%);
        }
        .table-hover tbody tr:hover {
            background-color: rgba(102, 126, 234, 0.05);
        }
        .loading-spinner {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 3px solid rgba(255,255,255,.3);
            border-radius: 50%;
            border-top-color: #fff;
            animation: spin 1s ease-in-out infinite;
        }
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        .fade-enter-active, .fade-leave-active {
            transition: opacity 0.5s;
        }
        .fade-enter, .fade-leave-to {
            opacity: 0;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container-fluid">
            <a class="navbar-brand fw-bold" href="../dashboard.php">
                <i class="bi bi-shield-lock-fill me-2"></i>
                SmartAccess UCB
            </a>
            
            <div class="collapse navbar-collapse">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="../dashboard.php">
                            <i class="bi bi-speedometer2 me-1"></i>Tableau de bord
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="etudiants.php">
                            <i class="bi bi-people me-1"></i>Étudiants
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="salles.php">
                            <i class="bi bi-building me-1"></i>Salles
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="acces.php">
                            <i class="bi bi-key me-1"></i>Accès
                        </a>
                    </li>
                </ul>
                
                <ul class="navbar-nav">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="bi bi-person-circle me-1"></i>
                            <?= htmlspecialchars($admin['prenom'] . ' ' . $admin['nom']) ?>
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="../logout.php">
                                <i class="bi bi-box-arrow-right me-2"></i>Déconnexion
                            </a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container-fluid py-4" id="app">
        <!-- En-tête -->
        <div class="row mb-4">
            <div class="col">
                <h1 class="h3 mb-1">Gestion des Étudiants</h1>
                <p class="text-muted">Ajouter, modifier et gérer les étudiants du système</p>
            </div>
        </div>

        <!-- Formulaire d'ajout/modification -->
        <div class="row mb-4">
            <div class="col-lg-4">
                <div class="content-card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="bi bi-person-plus me-2"></i>
                            {{ editingStudent ? 'Modifier Étudiant' : 'Ajouter Étudiant' }}
                        </h5>
                    </div>
                    <div class="card-body">
                        <form @submit.prevent="saveStudent">
                            <!-- Recherche par matricule UCB -->
                            <div class="mb-3">
                                <label class="form-label">Matricule UCB</label>
                                <div class="input-group">
                                    <input type="text" 
                                           class="form-control" 
                                           v-model="form.matricule"
                                           placeholder="Ex: 05/23/001"
                                           pattern="^\d{2}/\d{2}/\d{3}$"
                                           required>
                                    <button type="button" 
                                            class="btn btn-outline-primary" 
                                            @click="importFromUCB"
                                            :disabled="loading || !form.matricule">
                                        <span v-if="loading" class="loading-spinner"></span>
                                        <i v-else class="bi bi-download"></i>
                                        Importer UCB
                                    </button>
                                </div>
                                <small class="form-text text-muted">
                                    Format: XX/YY/ZZZ (ex: 05/23/001)
                                </small>
                            </div>

                            <!-- Informations personnelles -->
                            <div class="mb-3">
                                <label class="form-label">Nom</label>
                                <input type="text" 
                                       class="form-control" 
                                       v-model="form.nom"
                                       required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Prénom</label>
                                <input type="text" 
                                       class="form-control" 
                                       v-model="form.prenom"
                                       required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Email</label>
                                <input type="email" 
                                       class="form-control" 
                                       v-model="form.email">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Faculté</label>
                                <input type="text" 
                                       class="form-control" 
                                       v-model="form.faculte">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Promotion</label>
                                <input type="text" 
                                       class="form-control" 
                                       v-model="form.promotion">
                            </div>

                            <!-- Boutons d'action -->
                            <div class="d-grid gap-2">
                                <button type="submit" 
                                        class="btn btn-primary"
                                        :disabled="loading">
                                    <span v-if="loading" class="loading-spinner me-2"></span>
                                    <i v-else class="bi bi-check-lg me-2"></i>
                                    {{ editingStudent ? 'Mettre à jour' : 'Ajouter' }}
                                </button>
                                <button type="button" 
                                        class="btn btn-secondary"
                                        @click="resetForm"
                                        v-if="editingStudent">
                                    <i class="bi bi-x-lg me-2"></i>
                                    Annuler
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Liste des étudiants -->
            <div class="col-lg-8">
                <div class="content-card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="bi bi-people me-2"></i>
                            Liste des Étudiants ({{ students.length }})
                        </h5>
                        <button class="btn btn-light btn-sm" @click="loadStudents">
                            <i class="bi bi-arrow-clockwise"></i>
                        </button>
                    </div>
                    <div class="card-body p-0">
                        <!-- Barre de recherche -->
                        <div class="p-3 border-bottom">
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="bi bi-search"></i>
                                </span>
                                <input type="text" 
                                       class="form-control" 
                                       placeholder="Rechercher par matricule, nom, prénom ou email..."
                                       v-model="searchTerm"
                                       @input="searchStudents">
                            </div>
                        </div>

                        <!-- Tableau des étudiants -->
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Matricule</th>
                                        <th>Nom Complet</th>
                                        <th>Email</th>
                                        <th>Faculté</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-if="loading && students.length === 0">
                                        <td colspan="5" class="text-center py-4">
                                            <div class="loading-spinner me-2"></div>
                                            Chargement des étudiants...
                                        </td>
                                    </tr>
                                    <tr v-else-if="filteredStudents.length === 0">
                                        <td colspan="5" class="text-center py-4 text-muted">
                                            <i class="bi bi-inbox fs-1 mb-3"></i>
                                            <p>Aucun étudiant trouvé.</p>
                                        </td>
                                    </tr>
                                    <tr v-for="student in filteredStudents" :key="student.id">
                                        <td>
                                            <span class="badge bg-primary">{{ student.matricule }}</span>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="avatar-sm bg-light rounded-circle d-flex align-items-center justify-content-center me-2">
                                                    <i class="bi bi-person"></i>
                                                </div>
                                                <div>
                                                    <div class="fw-semibold">{{ student.nom }} {{ student.prenom }}</div>
                                                    <small class="text-muted">{{ student.promotion }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>{{ student.email || '-' }}</td>
                                        <td>{{ student.faculte || '-' }}</td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <button class="btn btn-outline-primary" 
                                                        @click="editStudent(student)"
                                                        title="Modifier">
                                                    <i class="bi bi-pencil"></i>
                                                </button>
                                                <button class="btn btn-outline-danger" 
                                                        @click="deleteStudent(student)"
                                                        title="Supprimer">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Alertes -->
        <transition name="fade">
            <div v-if="alert.show" 
                 :class="['alert', 'alert-' + alert.type, 'alert-dismissible', 'fade', 'show']" 
                 role="alert">
                <i :class="['bi', alert.type === 'success' ? 'bi-check-circle' : 'bi-exclamation-triangle', 'me-2']"></i>
                {{ alert.message }}
                <button type="button" class="btn-close" @click="hideAlert"></button>
            </div>
        </transition>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>
    <script>
        const { createApp } = Vue;

        createApp({
            data() {
                return {
                    students: [],
                    filteredStudents: [],
                    searchTerm: '',
                    loading: false,
                    editingStudent: null,
                    form: {
                        matricule: '',
                        nom: '',
                        prenom: '',
                        email: '',
                        faculte: '',
                        promotion: ''
                    },
                    alert: {
                        show: false,
                        type: 'success',
                        message: ''
                    }
                }
            },
            mounted() {
                this.loadStudents();
            },
            methods: {
                async loadStudents() {
                    this.loading = true;
                    try {
                        const response = await fetch('../api/students.php');
                        const data = await response.json();
                        
                        if (data.success) {
                            this.students = data.students;
                            this.filteredStudents = [...this.students];
                        } else {
                            this.showAlert('error', 'Erreur lors du chargement des étudiants');
                        }
                    } catch (error) {
                        console.error('Erreur:', error);
                        this.showAlert('danger', 'Erreur de connexion');
                    } finally {
                        this.loading = false;
                    }
                },

                async importFromUCB() {
                    if (!this.form.matricule) {
                        this.showAlert('warning', 'Veuillez saisir un matricule');
                        return;
                    }

                    this.loading = true;
                    try {
                        const response = await fetch(`https://akhademie.ucbukavu.ac.cd/api/v1/school-students/read-by-matricule?matricule=${this.form.matricule}`);
                        const data = await response.json();
                        
                        if (data && data.student) {
                            const student = data.student;
                            this.form.nom = student.nom || '';
                            this.form.prenom = student.prenom || '';
                            this.form.email = student.email || '';
                            this.form.faculte = student.faculte || '';
                            this.form.promotion = student.promotion || '';
                            
                            this.showAlert('success', 'Données importées avec succès depuis UCB');
                        } else {
                            this.showAlert('warning', 'Aucun étudiant trouvé avec ce matricule dans la base UCB');
                        }
                    } catch (error) {
                        console.error('Erreur import UCB:', error);
                        this.showAlert('danger', 'Erreur lors de l\'import depuis UCB');
                    } finally {
                        this.loading = false;
                    }
                },

                async saveStudent() {
                    this.loading = true;
                    try {
                        const url = this.editingStudent ? '../api/students.php' : '../api/students.php';
                        const method = this.editingStudent ? 'PUT' : 'POST';
                        
                        const formData = { ...this.form };
                        if (this.editingStudent) {
                            formData.id = this.editingStudent.id;
                        }

                        const response = await fetch(url, {
                            method: method,
                            headers: {
                                'Content-Type': 'application/json',
                            },
                            body: JSON.stringify(formData)
                        });

                        const data = await response.json();
                        
                        if (data.success) {
                            this.showAlert('success', this.editingStudent ? 'Étudiant modifié avec succès' : 'Étudiant ajouté avec succès');
                            this.resetForm();
                            this.loadStudents();
                        } else {
                            this.showAlert('danger', data.message || 'Erreur lors de l\'enregistrement');
                        }
                    } catch (error) {
                        console.error('Erreur:', error);
                        this.showAlert('danger', 'Erreur de connexion');
                    } finally {
                        this.loading = false;
                    }
                },

                editStudent(student) {
                    this.editingStudent = student;
                    this.form = { ...student };
                },

                async deleteStudent(student) {
                    if (!confirm(`Êtes-vous sûr de vouloir supprimer l'étudiant ${student.nom} ${student.prenom} ?`)) {
                        return;
                    }

                    try {
                        const response = await fetch('../api/students.php', {
                            method: 'DELETE',
                            headers: {
                                'Content-Type': 'application/json',
                            },
                            body: JSON.stringify({ id: student.id })
                        });

                        const data = await response.json();
                        
                        if (data.success) {
                            this.showAlert('success', 'Étudiant supprimé avec succès');
                            this.loadStudents();
                        } else {
                            this.showAlert('danger', data.message || 'Erreur lors de la suppression');
                        }
                    } catch (error) {
                        console.error('Erreur:', error);
                        this.showAlert('danger', 'Erreur de connexion');
                    }
                },

                resetForm() {
                    this.editingStudent = null;
                    this.form = {
                        matricule: '',
                        nom: '',
                        prenom: '',
                        email: '',
                        faculte: '',
                        promotion: ''
                    };
                },

                searchStudents() {
                    if (!this.searchTerm) {
                        this.filteredStudents = [...this.students];
                        return;
                    }

                    const term = this.searchTerm.toLowerCase();
                    this.filteredStudents = this.students.filter(student => 
                        student.matricule.toLowerCase().includes(term) ||
                        student.nom.toLowerCase().includes(term) ||
                        student.prenom.toLowerCase().includes(term) ||
                        (student.email && student.email.toLowerCase().includes(term))
                    );
                },

                showAlert(type, message) {
                    this.alert = {
                        show: true,
                        type: type,
                        message: message
                    };
                    
                    setTimeout(() => {
                        this.hideAlert();
                    }, 5000);
                },

                hideAlert() {
                    this.alert.show = false;
                }
            }
        }).mount('#app');
    </script>
</body>
</html>
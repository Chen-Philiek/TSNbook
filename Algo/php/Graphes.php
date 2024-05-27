<?php
session_start();
// Connexion à la base de données
require_once 'config.php';


if (isset($_SESSION['user'])) {
    $token = $_SESSION['user'];

    // Recherchez l'ID de l'utilisateur à partir du token dans la base de données
    $userIdQuery = "SELECT user_id FROM users WHERE token = :token";
    $userIdStmt = $bdd->prepare($userIdQuery);
    $userIdStmt->bindParam(':token', $token, PDO::PARAM_STR);
    $userIdStmt->execute();
    $userData = $userIdStmt->fetch(PDO::FETCH_ASSOC);

    if ($userData) {
        $userId = $userData['user_id'];
        // Récupérer les utilisateurs
        $queryUsers = "SELECT * FROM users";
        $stmtUsers = $bdd->query($queryUsers);
        $users = $stmtUsers->fetchAll(PDO::FETCH_ASSOC);

        // Récupérer les relations d'amitié
        $queryFriendships = "SELECT * FROM friendships";
        $stmtFriendships = $bdd->query($queryFriendships);
        $friendships = $stmtFriendships->fetchAll(PDO::FETCH_ASSOC);

        // Fermer la connexion à la base de données
        $bdd = null;

        // Convertir les données en format JSON
        $usersJSON = json_encode($users);
        $friendshipsJSON = json_encode($friendships);

    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Temporal Graph Display</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/d3/7.0.0/d3.min.js"></script>
    <link rel="stylesheet" href="../css/global1.css">
</head>
<body>
    <h1 id="graph-title">Temporal Graph Display</h1>
    <div id="graph-container"></div>
    <button id="open-modal-btn">Show the second graph</button>

    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }

        .backtohome-btn {
            margin-top: 20px;
            padding: 10px;
            display: block;
            margin-left: auto;
            margin-right: auto;
        }
        #open-modal-btn{
            margin-top: 20px;
            padding: 10px;
            display: block;
            margin-left: auto;
            margin-right: auto;
        }

        #graph-title {
            text-align: center;
            margin-top: 20px;
            background-color: #007bff;
            color: #fff;
            margin-bottom: 20px;
            width: 100%;
        }

        #graph-container {
            width: 100%;
            height: 600px; /* Ajuster la hauteur si nécessaire */
        }

        .highlighted {
            fill: red;
        }

        .arrow {
            fill: #999;
        }

        .unidirectional {
            marker-end: url(#arrow);
        }

        .bidirectional {
            marker-end: url(#arrow);
            marker-start: url(#arrow-reverse);
        }

        .modal {
            display: none;
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgb(0,0,0);
            background-color: rgba(0,0,0,0.4);
        }

        .modal-content {
            background-color: #fefefe;
            margin: 15% auto;
            padding: 20px;
            height: 80%;
            border: 1px solid #888;
            width: 80%;
        }
        
    </style>

    <svg style="display: none;">
        <defs>
            <marker id="arrow" viewBox="0 0 10 10" refX="5" refY="5"
                    markerWidth="6" markerHeight="6"
                    orient="auto-start-reverse">
                <path class="arrow" d="M 0 0 L 10 5 L 0 10 z" />
            </marker>
            <marker id="arrow-reverse" viewBox="0 0 10 10" refX="5" refY="5"
                    markerWidth="6" markerHeight="6"
                    orient="auto-start-reverse">
                <path class="arrow" d="M 10 0 L 0 5 L 10 10 z" />
            </marker>
        </defs>
    </svg>

    <div id="second-graph-modal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2>Second Graph</h2>
            <div id="second-graph-container"></div>
        </div>
    </div>

    <script>
        // Fonction pour créer le deuxième graphique dans la fenêtre modale
        function createSecondGraph() {
            // Données depuis PHP
            const users = <?php echo $usersJSON; ?>;
            const friendships = <?php echo $friendshipsJSON; ?>;
            const connectedUserId = <?php echo $userId; ?>; // En supposant que vous avez l'ID de l'utilisateur connecté

            // Création d'un objet pour stocker les positions des nœuds (utilisateurs)
            const nodePositions = {};

            // Calcul des positions des nœuds pour les afficher sur une grille
            const spacingX = 100; // Espacement horizontal entre les nœuds
            const spacingY = 100; // Espacement vertical entre les nœuds

            // Attribution des positions aux nœuds de manière aléatoire
            users.forEach((user, index) => {
                let x = Math.random() * 800; // Position horizontale aléatoire dans une plage de 800 pixels
                let y = Math.random() * 500; // Position verticale aléatoire dans une plage de 500 pixels
                nodePositions[user.user_id] = { x, y }; // Stockage de la position
            });

            // Création de l'élément SVG pour le graphe
            const svg = d3.select('#second-graph-container').append('svg')
                .attr('width', 800) // Largeur fixe de 800 pixels
                .attr('height', 500); // Hauteur fixe de 500 pixels

            // Création des nœuds du graphe (utilisateurs) avec les noms affichés
            svg.selectAll('.node')
                .data(users)
                .enter().append('g')
                .attr('class', d => d.user_id === connectedUserId ? 'node-group connected-user' : 'node-group') // Ajouter une classe spéciale pour l'utilisateur connecté
                .attr('transform', d => `translate(${nodePositions[d.user_id].x},${nodePositions[d.user_id].y})`) // Positionnement des nœuds
                .each(function(d) {
                    d3.select(this).append('circle')
                        .attr('class', 'node')
                        .attr('r', 10)
                        .style('fill', d => d.user_id === connectedUserId ? 'red' : 'blue'); // Mettre en rouge le nœud de l'utilisateur connecté

                   // Ajout du texte du nom à droite du nœud
                    d3.select(this).append('text')
                        .attr('class', 'node-label')
                        .attr('x', 0) // Décalage horizontal du texte
                        .attr('dy', 25) // Décalage vertical du texte
                        .text(d.full_name); // Affichage du nom de l'utilisateur
                });

            // Création des flèches pour représenter les relations d'amitié
            svg.selectAll('.link')
                .data(friendships)
                .enter().append('defs')
                .append('marker')
                .attr('id', (d, i) => 'arrow' + i) // Id unique pour chaque flèche
                .attr('markerWidth', 10)
                .attr('markerHeight', 10)
                .attr('refX', 9)
                .attr('refY', 3)
                .attr('orient', 'auto')
                .append('path')
                .attr('d', 'M0,0 L0,6 L9,3 z'); // Forme de la flèche

            // Création des liens du graphe avec des flèches
            svg.selectAll('.link')
                .data(friendships)
                .enter().append('line')
                .attr('class', 'link')
                .attr('x1', d => nodePositions[d.user_id].x)
                .attr('y1', d => nodePositions[d.user_id].y)
                .attr('x2', d => nodePositions[d.friend_id].x)
                .attr('y2', d => nodePositions[d.friend_id].y)
                .attr('marker-end', (d, i) => 'url(#arrow' + i + ')') // Utilisation de la flèche correspondante
                .style('stroke', 'black')
                .style('stroke-width', 2);
        }

        // Ouvrir la fenêtre modale lorsque le bouton est cliqué
        document.getElementById('open-modal-btn').addEventListener('click', function() {
            createSecondGraph(); // Créer le deuxième graphique dans la fenêtre modale
            document.getElementById('second-graph-modal').style.display = "block";
        });

        // Fermer la fenêtre modale lorsque l'utilisateur clique sur la croix
        document.getElementsByClassName('close')[0].addEventListener('click', function() {
            document.getElementById('second-graph-modal').style.display = "none";
            // Supprimer le contenu du deuxième graphique lors de la fermeture de la fenêtre modale
            d3.select('#second-graph-container').select('svg').remove();
        });

        // Fermer la fenêtre modale lorsque l'utilisateur clique en dehors de celle-ci
        window.onclick = function(event) {
            if (event.target == document.getElementById('second-graph-modal')) {
                document.getElementById('second-graph-modal').style.display = "none";
                // Supprimer le contenu du deuxième graphique lors de la fermeture de la fenêtre modale
                d3.select('#second-graph-container').select('svg').remove();
            }
        };

     
        // Temporal force-directed graph function
        function createTemporalGraph({ nodes, links }) {
            const width = document.getElementById('graph-container').offsetWidth;
            const height = 600; // Hauteur fixe

            const simulation = d3.forceSimulation(nodes)
                .force("charge", d3.forceManyBody())
                .force("link", d3.forceLink(links).id(d => d.id))
                .force("x", d3.forceX(width / 2))
                .force("y", d3.forceY(height / 2));

            const svg = d3.select('#graph-container').append('svg')
                .attr("viewBox", [0, 0, width, height])
                .attr("width", width)
                .attr("height", height)
                .attr("style", "max-width: 100%; height: auto;");

            const link = svg.append("g")
                .selectAll("line")
                .data(links)
                .enter().append("line")
                .attr("stroke", "#999")
                .attr("stroke-opacity", 0.6)
                .attr("class", d => d.bidirectional ? "bidirectional" : "unidirectional");

            const node = svg.append("g")
                .selectAll("circle")
                .data(nodes)
                .enter().append("circle")
                .attr("r", 5)
                .attr("fill", d => d.connected ? "red" : "blue")
                .attr("class", d => d.connected ? "highlighted" : "")
                .call(d3.drag()
                    .on("start", dragstarted)
                    .on("drag", dragged)
                    .on("end", dragended));

            // node.append("title")
            //     .text(d => d.name);

            // const text = svg.append("g")
            //     .selectAll("text")
            //     .data(nodes)
            //     .enter().append("text")
            //     .attr("text-anchor", "middle")
            //     .attr("dy", -8)
            //     .text(d => d.name);

            simulation.on("tick", () => {
                link.attr("x1", d => d.source.x)
                    .attr("y1", d => d.source.y)
                    .attr("x2", d => d.target.x)
                    .attr("y2", d => d.target.y);

                node.attr("cx", d => d.x)
                    .attr("cy", d => d.y);

                text.attr("x", d => d.x)
                    .attr("y", d => d.y);
            });

            function dragstarted(event) {
                if (!event.active) simulation.alphaTarget(0.3).restart();
                event.subject.fx = event.subject.x;
                event.subject.fy = event.subject.y;
            }

            function dragged(event) {
                event.subject.fx = event.x;
                event.subject.fy = event.y;
            }

            function dragended(event) {
                if (!event.active) simulation.alphaTarget(0);
                event.subject.fx = null;
                event.subject.fy = null;
            }

            return svg.node();
        }

        // Données depuis PHP
        const users = <?php echo $usersJSON; ?>;
        const friendships = <?php echo $friendshipsJSON; ?>;
        const connectedUserId = <?php echo $userId; ?>; // En supposant que vous avez l'ID de l'utilisateur connecté

        // Convertir le format des données pour D3.js
        const nodes = users.map(user => ({ id: user.user_id, name: user.full_name, connected: user.user_id === connectedUserId }));
        const links = friendships.map(friendship => ({
            source: nodes.find(node => node.id === friendship.user_id),
            target: nodes.find(node => node.id === friendship.friend_id),
            bidirectional: friendships.some(f => f.user_id === friendship.friend_id && f.friend_id === friendship.user_id)
        }));

        // Créer le graphe temporel
        const graph = createTemporalGraph({ nodes, links });

      
      
    </script>

    <?php echo '<button class="backtohome-btn" onclick="window.location.href=\'home.php\'">Back</button>'; ?>
    <script src="../javascript/logout.js"></script>
</body>
</html>

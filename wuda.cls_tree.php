<?php
  /*******************************************************
    Classname: tree
  ********************************************************
    Controls and handles data of nested sets
    in MySQL/SQLite and PHP.
  ********************************************************

    Copyright (C) 2015  http://wuda.at/ and http://wudablog.de1.cc/

    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>.
    
  ********************************************************/
  class tree {
    private $conn = null;
    private $data = null;
    private $table = '';

    public function __construct($database, $table) {
      $this->conn = new SQLite3($database);
      $this->table = $table;
      $this->conn->exec("CREATE TABLE IF NOT EXISTS ".$this->table." (
                  name VARCHAR(512) NOT NULL,
                  lft INT UNSIGNED NOT NULL,
                  rgt INT UNSIGNED NOT NULL
                );");
      @$this->conn->exec("INSERT INTO ".$this->table." (rowid, name, lft, rgt) VALUES(1,'Root',1,2);");
    }
    public function loadTree() {
      $result = $this->conn->query("SELECT n.rowid, n.name, COUNT(*)-1 AS level FROM "
      .$this->table." AS n,".$this->table." AS p WHERE n.lft BETWEEN p.lft AND p.rgt "
      ."GROUP BY n.lft ORDER BY n.lft;");
      $this->data = null;
      if ($result) {
        while ($item = $result->fetchArray()) {
          $this->data[] = $item;
        }
      }
    }
    public function addItem($name, $parentid = 1) {
      $result = $this->conn->query("SELECT rgt FROM ".$this->table." WHERE rowid = $parentid;");
      if ($result) {
        $item = $result->fetchArray();
        $rgt = $item['rgt'];
        $this->conn->exec("LOCK TABLES ".$this->table.";");
        $this->conn->exec("UPDATE ".$this->table." SET rgt=rgt+2 WHERE rgt >= $rgt;");
        $this->conn->exec("UPDATE ".$this->table." SET lft=lft+2 WHERE lft > $rgt;");
        $this->conn->exec("INSERT INTO ".$this->table." (name,lft,rgt) VALUES ('$name', $rgt, $rgt+1);");
        $this->conn->exec("UNLOCK TABLES;");
      }
    }
    public function deleteItem($id) {
      if ($id == 1) return; // Root-Element can not be deleted
      $result = $this->conn->query("SELECT lft, rgt FROM ".$this->table." WHERE rowid = $id;");
      if ($result) {
        $item = $result->fetchArray();
        $rgt = $item['rgt'];
        $lft = $item['lft'];
        $this->conn->exec("DELETE FROM ".$this->table." WHERE lft BETWEEN $lft AND $rgt;");
        $this->conn->exec("UPDATE tree SET lft=lft-ROUND(($rgt-$lft+1)) WHERE lft>$rgt;");
        $this->conn->exec("UPDATE tree SET rgt=rgt-ROUND(($rgt-$lft+1)) WHERE rgt>$rgt;");
      }
    }
    public function hasChildren($id) {
      $result = $this->conn->query("SELECT ((rgt-lft-1)>0) AS cnt FROM ".$this->table." WHERE rowid = $id;");
      if ($result) {
        $item = $result->fetchArray();
        return (bool)$item['cnt'];
      }
    }
    public function getHTML() {
      $level = -1;
      $html = "";
      foreach ($this->data as $row) {
        if($row['level'] < $level) {
        $diff = $level - $row['level'];
        $html .= str_repeat("</ul>", $diff);
        }
        if($row['level'] > $level){ $html .= "<ul>"; }
        $html .= "<li>[".$row['rowid']."] ".$row['name']."</li>";
        $level = $row['level'];
      }
      if ($level >= 0) $html .= str_repeat("</ul>", ($level + 1));
      return $html;
    }
  }
?>
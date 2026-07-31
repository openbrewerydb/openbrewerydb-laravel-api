# Introduction

Open Brewery DB is a free dataset and API with public information on breweries, cideries, brewpubs, and bottleshops

<aside>
    <strong>Base URL</strong>: <code>https://api.openbrewerydb.org</code>
</aside>

<h2>The Goal</h2>
<p>
The goal of Open Brewery DB is to maintain an open-source,
community-driven dataset and provide a public API for brewery-related data.
</p>

<h2>The Mission</h2>
<p>
It is our belief that public information should be freely accessible for
the betterment of the community and the happiness of web developers and data analysts.
</p>

## Model Context Protocol

Open Brewery DB provides a public, read-only [Model Context Protocol](https://modelcontextprotocol.io/)
server so AI agents can search and explore the brewery dataset without translating requests into
REST API calls.

<aside>
    <strong>MCP URL</strong>: <code>https://api.openbrewerydb.org/mcp</code>
</aside>

The server uses Streamable HTTP and does not require authentication. Requests are limited to 60 per
minute per IP address. Paginated tools return 10 results by default and accept at most 50 results per
page.

### Available tools

| Tool | Description |
| --- | --- |
| `list-breweries` | Browse breweries with structured location, type, name, ID, distance, and sorting filters. |
| `get-brewery` | Retrieve a brewery by UUID. |
| `search-breweries` | Perform full-text brewery searches. |
| `get-brewery-metadata` | Count filtered breweries by state or province, country, and brewery type. |

### Claude

In Claude Desktop or Claude on the web, open **Settings**, select **Connectors**, choose
**Add custom connector**, and enter `https://api.openbrewerydb.org/mcp`.

Claude Code users can connect from a terminal:

```shell
claude mcp add --transport http open-brewery-db https://api.openbrewerydb.org/mcp
```

### OpenCode

Add the remote server to `opencode.json`:

```json
{
  "$schema": "https://opencode.ai/config.json",
  "mcp": {
    "open-brewery-db": {
      "type": "remote",
      "url": "https://api.openbrewerydb.org/mcp",
      "enabled": true
    }
  }
}
```

### VS Code

Add the HTTP server to your user or workspace `mcp.json`:

```json
{
  "servers": {
    "open-brewery-db": {
      "type": "http",
      "url": "https://api.openbrewerydb.org/mcp"
    }
  }
}
```

### Example prompts

- Find microbreweries in Portland, Oregon.
- Search for breweries containing "Breakside" in their name.
- Get the brewery with ID `b54b16e1-ac3b-4bff-a11f-f7ae9ddc27e0`.
- Count breweries in Canada by province and brewery type.

If a client receives HTTP `429 Too Many Requests`, wait for the `Retry-After` period before making
more requests. MCP clients discover the current input and output schema for each tool directly from
the server.
